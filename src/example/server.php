#!php
<?php

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
// use Swoole\Process;/


$server = new Server("127.0.0.1", 9501);

$server->set([
    'worker_num' => 2,
    'task_worker_num' => 2,
]);

$server->on('Start', function ($server) {
    Swoole\Timer::tick(1000, function () use ($server) {
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
});


$server->on('WorkerStart', function ($server, $worker_id) {
    if ($worker_id < $server->setting['worker_num']) {
        // cli_set_process_title("php-swoole-worker-{$worker_id}");
        swoole_set_process_name("php $worker_id event aaaworker");
    } else {
        // cli_set_process_title("php-swoole-task-worker-" . ($worker_id - $server->setting['worker_num']));
        swoole_set_process_name("php $worker_id event aaatasker");
    }
});

// if ($worker_id < $server->setting['worker_num']) {
//     Process::setName("php-swoole-worker-{$worker_id}");
// } else {
//     Process::setName("php-swoole-task-worker-" . ($worker_id - $server->setting['worker_num']));
// }
// if($worker_id >= $server->setting['worker_num']) {
//     swoole_set_process_name("php {$argv[0]} task worker");
// } else {
//     swoole_set_process_name("php {$argv[0]} event worker");
// }


$server->on('Request', function (Request $request, Response $response) use ($server) {
    $response->header('Content-Type', 'text/plain');
    $responseContent = "Welcome to Swoole!\n";

    go(function () use ($server, &$responseContent) {
        $httpClient = new Swoole\Coroutine\Http\Client('example.com', 80);
        $httpClient->set(['timeout' => 1]);
        $httpClient->get('/');
        $responseContent .= "Request 1 complete: HTTP Status {$httpClient->statusCode}\n";
    });

    // go(function () use ($server, &$responseContent) {
    //     $httpClient = new Swoole\Coroutine\Http\Client('example.org', 80);
    //     $httpClient->set(['timeout' => 1]);
    //     $httpClient->get('/');
    //     $responseContent .= "Request 2 complete: HTTP Status {$httpClient->statusCode}\n";
    // });

    $server->task("This is a task message");

    // 最後將所有響應數據寫入並結束響應
    $response->end($responseContent . "Request handled by worker\n");
});


$server->on('Task', function ($server, $task_id, $src_worker_id, $data) {
    echo "Task Received: {$data}\n";
    sleep(2);  // 模擬耗時操作
    return "Task Result";
});

// 當 Task Worker 完成任務後觸發
$server->on('Finish', function ($server, $task_id, $data) {
    echo "Task {$task_id} finished with result: {$data}\n";
});



echo 'server start ... http://localhost:9501' . PHP_EOL;
$server->start();
