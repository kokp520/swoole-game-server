<?php

use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function () {
        try {
            call_user_func($func);
        }
        catch (Error $e) {
            var_dump($e);
        }
        catch(Exception $e) {
            var_dump($e);
        }
    });

    //协程1的错误不影响协程2
    Coroutine::create(function () {
        Coroutine::sleep(5);
        echo 2;
    });
});
