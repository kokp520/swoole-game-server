<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;

class ShowInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:show-info {--s=} {--p=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '顯示laravels相關資訊';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $server = $this->option('s') ?? 'default-server';
        $port = $this->option('p') ?? 'default-port';

        $this->componentInfo();
        $this->protocolInfo($server, $port);
    }

    private function newTable()
    {
        return new Table($this->output);
    }

    private function protocolInfo($server = '', $port = '')
    {
        // 第二個表格：Protocols
        $this->info('>>> Protocols');
        $protocolsTable = $this->newTable();
        $protocolsTable
            ->setHeaders(['Protocol', 'Status', 'Handler', 'Listen At'])
            ->setRows([
                ['Lobby', 'On', "App\\Http\\Handlers\\{$server}Handler", "http://127.0.0.1:$port/lobby"],
                ['Gs', 'On', "App\\Http\\Handlers\\{$server}Handler", "http://127.0.0.1:$port/game"],
                ['Main HTTP', 'On', 'Laravel Router', 'http://127.0.0.1:5200'],
                ['Main WebSocket', 'On', 'App\Http\Handlers\WebSocketHandler', 'ws://127.0.0.1:5200'],
            ]);
        $protocolsTable->render();
    }

    private function componentInfo()
    {
        // 第一個表格：Components
        $this->info('>>> Components');
        $componentsTable = $this->newTable();
        $componentsTable
            ->setHeaders(['Component', 'Version'])
            ->setRows([
                ['PHP', phpversion()],
                // ['Swoole', '5.1.4'],
                // ['LaravelS', '3.8.2'],
                ['os', PHP_OS],
                ['Laravel Framework', app()->version()],
            ]);
        $componentsTable->render();
    }
}
