<?php

// namespace App\Sockets;

// use Hhxsv5\LaravelS\Swoole\Socket\TcpSocket;
// use Swoole\Server;

// class Tcp extends TcpSocket
// {
//     public function onConnect(Server $server, $fd, $reactorId)
//     {
//         \Log::info('new tcp connection', [$fd, $reactorId]);
//         $server->send($fd, 'welcome to laravls tcp server...');
//     }

//     //這些連接和主服務器上的 HTTP/WebSocket 連接共享 Worker 進程，因此可以在這些事件回調中使用 LaravelS 
//     // 提供的異步任務投遞、SwooleTable、Laravel 提供的組件如 DB、Eloquent 等
//     // 。同時，如果需要使用該協議端口的 Swoole\Server\Port 對象，只需要像如下代碼一樣訪問 Socket 類的成員 swoolePort 即可。
//     public function onReceive(Server $server, $fd, $reactorId, $data)
//     {
//         \Log::info('Received data', [$fd, $data]);
//         $server->send($fd, 'LaravelS: ' . $data);
//         if ($data === "quit\r\n") {
//             $server->send($fd, 'LaravelS: bye' . PHP_EOL);
//             $server->close($fd);
//         }

//         // get port 
//         $port = $this->swoolePort;
//     }

//     public function onClose(Server $server, $fd, $reactorId)
//     {
//         \Log::info('Close TCP connection', [$fd]);
//         $server->send($fd, 'Goodbye');
//     }
// }
