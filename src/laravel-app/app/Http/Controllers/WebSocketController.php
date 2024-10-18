<?php

namespace App\Http\Controllers;

use Swoole\WebSocket\Server;

class WebSocketController
{
    public function onOpen(Server $server, $request)
    {
        // 當 WebSocket 連接建立時執行
        $server->push($request->fd, "Welcome to WebSocket server");
    }

    public function onMessage(Server $server, $frame)
    {
        // 當收到消息時執行
        // $server->push($frame->fd, "Received: {$frame->data}");
        // 解析收到的消息
        $data = json_decode($frame->data, true);

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

    public function onClose(Server $server, $fd)
    {
        // 當 WebSocket 連接關閉時執行
    }
}
