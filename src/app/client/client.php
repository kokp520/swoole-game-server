<?php

$client = new Swoole\WebSocket\Client('ws://127.0.0.1:9501');

// 連接到 Server
$client->connect();

// 進入大廳後請求大廳用戶列表
$client->push(json_encode(['action' => 'get_lobby_users']));

// 接收消息
$client->recv(function($response) {
    echo "收到消息: {$response}\n";
    
    $data = json_decode($response, true);
    if ($data['action'] === 'lobby_users') {
        echo "當前大廳用戶: " . json_encode($data['users']) . "\n";
        
        // 模擬進入遊戲
        global $client;
        $client->push(json_encode(['action' => 'enter_game', 'game_id' => 1]));
    } elseif ($data['action'] === 'enter_game') {
        echo "進入遊戲: {$data['message']}\n";
    }
});

$client->close();
