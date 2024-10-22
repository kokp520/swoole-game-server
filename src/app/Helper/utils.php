<?php

function delAlreadyUsedProcess($port, $pidfile)
{
    $cmd = "lsof -i :$port -t";
    $pid = shell_exec($cmd);

    if ($pid) {
        $pidList = explode("\n", trim($pid));
        foreach ($pidList as $pid) {
            exec("kill -9 $pid");
        }
        `sleep 0.1`;
    }
}
