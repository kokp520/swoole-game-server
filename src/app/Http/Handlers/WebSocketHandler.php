<?php

namespace App\Http\Handlers;

use Swoole\WebSocket\Server;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;

class WebSocketHandler implements WebSocketHandlerInterface
{
    private $players = []; // 保存所有玩家的座標
    private $server;

    public function __construct()
    {
        echo "WebSocketHandler init" . PHP_EOL;
        $this->startMonitoring(); // 啟動監控 Worker 和 Process 數量

    }

    public function onOpen(Server $server, Request $request)
    {
        if (!$this->server) {
            $this->server = $server;
        }
        // 每個新的連接都代表一個新的玩家
        $this->players[$request->fd] = [
            'x' => 200,
            'y' => 200,
            'color' => 'blue', // 默認顏色，可以隨機分配不同顏色給不同玩家
        ];

        // 廣播給所有人有新玩家加入
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
        // 解析收到的消息
        $data = json_decode($frame->data, true);
        echo ("recieve: data: " . json_encode($data) . PHP_EOL);

        if (isset($data['cmd'])) {
            // 根據命令移動玩家
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
        // 當玩家退出時，移除該玩家並廣播
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
                echo json_encode($stats).PHP_EOL;
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
