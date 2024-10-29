<?php

namespace App\Http\Handlers;

use Swoole\WebSocket\Server;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;

class WebSocketHandler implements WebSocketHandlerInterface
{
    private $players = [];
    private $server;
    private const GRAVITY = 0.5; // 重力
    private const FRICTION = 0.8; // 摩擦力
    private const JUMP_STRENGTH = -10; // 跳躍力
    private const MOVE_SPEED = 5; // 移動速度
    private const TICK_RATE = 60; // 每秒60次更新

    public function __construct()
    {
        echo "WebSocketHandler init" . PHP_EOL;
        $this->startMonitoring();
        $this->startGameLoop();
    }

    public function onOpen(Server $server, Request $request)
    {
        if (!$this->server) {
            $this->server = $server;
        }

        echo ("new user connect, fd : $request->fd " . PHP_EOL);
    }

    public function onMessage(Server $server, Frame $frame)
    {
        $data = json_decode($frame->data, true);
        echo ("receive: data: " . json_encode($data) . PHP_EOL);

        if (isset($data['cmd'])) {
            switch ($data['cmd']) {
                case 'newplayer':
                    $this->players[$frame->fd] = [
                        'x' => 200,
                        'y' => 200,
                        'color' => $data['color'] ?? 'blue',
                        'name' => $data['name'] ?? 'Player',
                        'velocityX' => 0,
                        'velocityY' => 0,
                        'isJumping' => false
                    ];
                    $this->broadcastNewState($server, 'newPlayer');
                    break;
                case 'left':
                    $this->players[$frame->fd]['velocityX'] = -self::MOVE_SPEED;
                    break;
                case 'right':
                    $this->players[$frame->fd]['velocityX'] = self::MOVE_SPEED;
                    break;
                case 'jump':
                    if (!$this->players[$frame->fd]['isJumping']) {
                        $this->players[$frame->fd]['velocityY'] = self::JUMP_STRENGTH;
                        $this->players[$frame->fd]['isJumping'] = true;
                    }
                    break;
                case 'disconnect':
                    $this->onClose($server, $frame->fd, null);
                    break;
                default:
                    $server->push($frame->fd, json_encode(['message' => 'Unknown command']));
                    return;
            }

            $this->applyPhysics($frame->fd);
            $this->broadcastNewState($server, 'updatePosition');
        }
    }

    public function onClose(Server $server, $fd, $reactorId)
    {
        unset($this->players[$fd]);
        $this->broadcastNewState($server, 'playerLeft');
        echo "websocket connection closed: fd $fd\n";
    }

    private function broadcastNewState($server, $type)
    {
        foreach ($server->connections as $fd) {
            if ($server->isEstablished($fd)) {
                $server->push($fd, json_encode([
                    'type' => $type,
                    'data' => ['players' => $this->players]
                ]));
            }
        }
    }

    private function applyPhysics($playerId)
    {
        // 檢查玩家是否存在
        if (!isset($this->players[$playerId])) {
            return;
        }

        $player = &$this->players[$playerId];

        // 確保 velocityX 和 velocityY 被初始化
        if (!isset($player['velocityX'])) {
            $player['velocityX'] = 0;
        }
        if (!isset($player['velocityY'])) {
            $player['velocityY'] = 0;
        }

        // 現有的物理運算邏輯
        $player['velocityY'] += self::GRAVITY;
        $player['y'] += $player['velocityY'];
        $player['x'] += $player['velocityX'];

        // Apply friction
        $player['velocityX'] *= self::FRICTION;

        // Check boundaries (assuming canvas size of 800x400)
        if ($player['x'] < 0) $player['x'] = 0;
        if ($player['x'] + 20 > 800) $player['x'] = 800 - 20;
        if ($player['y'] + 40 > 400) {
            $player['y'] = 400 - 40;
            $player['velocityY'] = 0;
            $player['isJumping'] = false;
        }

        // Check platform collisions
        $platforms = [
            ['x' => 0, 'y' => 350, 'width' => 800, 'height' => 50],
            ['x' => 200, 'y' => 250, 'width' => 100, 'height' => 20],
            ['x' => 400, 'y' => 200, 'width' => 100, 'height' => 20],
            ['x' => 600, 'y' => 150, 'width' => 100, 'height' => 20]
        ];

        foreach ($platforms as $platform) {
            if (
                $player['y'] + 40 > $platform['y'] &&
                $player['y'] < $platform['y'] + $platform['height'] &&
                $player['x'] + 20 > $platform['x'] &&
                $player['x'] < $platform['x'] + $platform['width']
            ) {
                $player['y'] = $platform['y'] - 40;
                $player['velocityY'] = 0;
                $player['isJumping'] = false;
                break;
            }
        }
    }


    private function startGameLoop()
    {
        swoole_timer_tick(1000 / self::TICK_RATE, function () {
            foreach ($this->players as $playerId => $player) {
                $this->applyPhysics($playerId);
            }
            $this->broadcastNewState($this->server, 'updatePosition');
        });
    }

    public function startMonitoring()
    {
        swoole_timer_tick(1000, function () {
            if ($this->server) {
                $stats = $this->server->stats();
                echo json_encode($stats) . PHP_EOL;
            }
        });
    }
}
