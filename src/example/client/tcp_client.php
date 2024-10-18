<?php

$client = new Swoole\Client(SWOOLE_SOCK_TCP);

// 嘗試連接伺服器
if (!$client->connect('127.0.0.1', 9501, -1)) {
    exit("connect failed. Error: {$client->errCode}\n");
}

echo "Connected to Swoole TCP server. You can start sending messages. Type 'exit' to quit.\n";

// 持續交互的循環
while (true) {
    // 從命令行讀取輸入
    echo "Enter message: ";
    $input = trim(fgets(STDIN)); // 從標準輸入獲取用戶輸入

    // 如果輸入是 'exit'，則退出循環
    if ($input === 'exit') {
        echo "Exiting...\n";
        break;
    }

    // 發送用戶輸入的數據到伺服器
    if (!$client->send($input)) {
        echo "Failed to send message. Error: {$client->errCode}\n";
        continue;
    }

    // 從伺服器接收數據
    $response = $client->recv();
    if ($response === false) {
        echo "Failed to receive message from server. Error: {$client->errCode}\n";
        continue;
    }

    // 顯示來自伺服器的回應
    echo "Received from server: $response\n";
}

// 關閉連接
$client->close();
