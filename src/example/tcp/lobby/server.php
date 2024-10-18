<?php

use Swoole\Server;

// 創建一個TCP伺服器
$server = new Server("0.0.0.0", 9501);

// 當伺服器啟動時
$server->on("start", function (Server $server) {
    echo "Swoole TCP server is started at 0.0.0.0:9501\n";
});

// 當接收到TCP連接數據時
$server->on("receive", function (Server $server, $fd, $reactorId, $data) {
    echo "Received from client: $data\n";
    $server->send($fd, "Hello, this is Swoole TCP Server");
});

// 啟動伺服器
$server->start();
