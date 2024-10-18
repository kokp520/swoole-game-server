<?php

function delAlreadyUsedProcess($port)
{
    $cmd = "netstat -tlnp | grep \":$port \" | awk '{print $7}' | awk -F\"/\" '{print $1}'";
    $pid = shell_exec($cmd);

    if ($pid) {
        $pidList = explode("\n", trim($pid));
        foreach ($pidList as $pid) {
            echo "Killing process with PID $pid on port $port" . PHP_EOL;
            exec("kill -9 $pid");
        }
        `sleep 0.1`;
    }
}