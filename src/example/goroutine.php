<?php

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$server = new Server("127.0.0.1", 9501);

// 設置 Worker 數量和 Task Worker 數量
$server->set([
    'worker_num' => 2,  // 兩個 Worker 進程
    'task_worker_num' => 2,  // 兩個 Task Worker 進程
]);

// 當有 HTTP 請求時觸發
$server->on('Request', function (Request $request, Response $response) use ($server) {
    $response->header('Content-Type', 'text/plain');
    $response->write("Welcome to Swoole!\n");

    // 使用協程並行處理兩個異步任務
    go(function() use ($server, $response) {
        $httpClient = new Swoole\Coroutine\Http\Client('example.com', 80);
        $httpClient->set(['timeout' => 1]);
        $httpClient->get('/');
        $response->write("Request 1 complete: HTTP Status {$httpClient->statusCode}\n");
    });

    go(function() use ($server, $response) {
        $httpClient = new Swoole\Coroutine\Http\Client('example.org', 80);
        $httpClient->set(['timeout' => 1]);
        $httpClient->get('/');
        $response->write("Request 2 complete: HTTP Status {$httpClient->statusCode}\n");
    });

    // 使用 Task Worker 處理耗時任務
    $server->task("This is a task message");

    $response->end("Request handled by worker\n");
});

// 當 Task Worker 接收到任務時觸發
$server->on('Task', function ($server, $task_id, $src_worker_id, $data) {
    echo "Task Received: {$data}\n";
    sleep(2);  // 模擬耗時操作
    return "Task Result";
});

// 當 Task Worker 完成任務後觸發
$server->on('Finish', function ($server, $task_id, $data) {
    echo "Task {$task_id} finished with result: {$data}\n";
});

// 定期顯示 Swoole 伺服器的狀態 (每 5 秒)
Swoole\Timer::tick(5000, function() use ($server) {
    $stats = $server->stats();
    echo "Current Swoole Stats:\n";
    echo "Connections: {$stats['connection_num']}\n";
    echo "Workers: {$stats['worker_num']}\n";
    echo "Task Workers: {$stats['task_worker_num']}\n";
    echo "Requests: {$stats['request_count']}\n";
    echo "Responses: {$stats['response_count']}\n";
    echo "Total Received Bytes: {$stats['total_recv_bytes']}\n";
    echo "Total Sent Bytes: {$stats['total_send_bytes']}\n";
});

$server->start();
