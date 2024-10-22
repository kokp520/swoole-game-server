<?php

namespace App\Http\Handlers;

use Swoole\WebSocket\Server;
use Swoole\Http\Request;
use Swoole\Websocket\Frame;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;

class WebSocketHandler implements WebSocketHandlerInterface
{

    private $server;

    public function __construct()
    {
        echo "WebSocketHandler init" . PHP_EOL;
        // create ...s
    }

    public function onOpen(Server $server, Request $request)
    {
        $this->pusher($server, $request->fd, "Welcome to websocket server");
        echo ("new user connect, fd : $request->fd " . PHP_EOL);
    }

    public function onMessage(Server $server, Frame $frame)
    {
        // 當收到消息時執行
        // $server->push($frame->fd, "Received: {$frame->data}");
        // 解析收到的消息
        $data = json_decode($frame->data, true);
        echo ("recieve: data: " . json_encode($data) . PHP_EOL);

        if (isset($data['cmd'])) {
            // 根據命令執行對應的動作
            switch ($data['cmd']) {
                case 'up':
                case 'down':
                case 'left':
                case 'right':
                    // 這裡可以處理移動邏輯，或進行廣播通知其他玩家
                    $server->push($frame->fd, json_encode(['message' => "Move: {$data['cmd']}"]));
                    break;
                default:
                    $server->push($frame->fd, json_encode(['message' => 'Unknown command']));
            }
        }
    }

    public function onClose(Server $server, $fd, $reactorId)
    {
        // 當 WebSocket 連接關閉時執行
        echo "websocket connection closed\n" . json_encode([
            'fd' => $fd,
            'server' => $server,
            'reactorId' => $reactorId
        ]);
    }

    public function pusher($server, $fd, $msg, $data = []) 
    {
        $server->push($fd, json_encode([
            'message' => $msg,
            'data' => $data
        ]));
    }
}
