<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $server = $this->option('s');
        $port = $this->option('p');
        $this->info('Laravels 相關資訊:');

        $this->info('額外資訊：');
        $this->info('PHP 版本: ' . phpversion());
        $this->info('Laravel 版本: ' . app()->version());
        $this->info('操作系統: ' . PHP_OS);
        $this->info("服務器：$server, Port: $port");
        $this->info("url: http://localhost:$port");
    }
}
