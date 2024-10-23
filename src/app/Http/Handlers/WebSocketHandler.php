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

    public function __construct()
    {
        echo "WebSocketHandler init" . PHP_EOL;
        $this->startMonitoring();
    }

    public function onOpen(Server $server, Request $request)
    {
        if (!$this->server) {
            $this->server = $server;
        }

        $this->players[$request->fd] = [
            'x' => 200,
            'y' => 200,
            'color' => 'blue',
        ];

        foreach ($server->connections as $fd) {
            if ($server->isEstablished($fd)) {
                $this->pusher($server, $fd, 'newPlayer', [
                    'players' => $this->players
                ]);
            }
        }

        echo ("new user connect, fd : $request->fd " . PHP_EOL);
    }

    public function onMessage(Server $server, Frame $frame)
    {
        $data = json_decode($frame->data, true);
        echo ("recieve: data: " . json_encode($data) . PHP_EOL);

        if (isset($data['cmd'])) {
            switch ($data['cmd']) {
                case 'up':
                    $this->players[$frame->fd]['y'] -= 10;
                    break;
                case 'down':
                    $this->players[$frame->fd]['y'] += 10;
                    break;
                case 'left':
                    $this->players[$frame->fd]['x'] -= 10;
                    break;
                case 'right':
                    $this->players[$frame->fd]['x'] += 10;
                    break;
                default:
                    $server->push($frame->fd, json_encode(['message' => 'Unknown command']));
                    return;
            }

            // 廣播所有玩家的最新位置給所有連接的客戶端
            foreach ($server->connections as $fd) {
                if ($server->isEstablished($fd)) {
                    $this->pusher($server, $fd, 'updatePosition', [
                        'players' => $this->players
                    ]);
                }
            }
        }
    }

    public function onClose(Server $server, $fd, $reactorId)
    {
        unset($this->players[$fd]);

        foreach ($server->connections as $clientFd) {
            if ($server->isEstablished($clientFd)) {
                $this->pusher($server, $clientFd, 'playerLeft', [
                    'players' => $this->players
                ]);
            }
        }

        echo "websocket connection closed: fd $fd\n";
    }

    public function pusher($server, $fd, $type, $data = [])
    {
        $server->push($fd, json_encode([
            'type' => $type,
            'data' => $data
        ]));
    }

    public function startMonitoring()
    {
        swoole_timer_tick(1000, function () {
            if ($this->server) {
                $stats = $this->server->stats();
                echo json_encode($stats) . PHP_EOL;
                //  foreach ($this->server->connections as $fd) {
                //      if ($this->server->isEstablished($fd)) {
                //          $this->pusher($this->server, $fd, 'serverStats', [
                //              'worker_num' => $stats['worker_num'] ?? 0,
                //              'active_worker_num' => $stats['active_worker_num'] ?? 0,
                //              'task_worker_num' => $stats['task_worker_num'] ?? 0,
                //              'connection_num' => $stats['connection_num'] ?? 0,
                //              'total_request_count' => $stats['request_count'] ?? 0
                //          ]);
                //      }
                //  }
            }
        });
    }
}
