<?php

namespace App;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Hhxsv5\LaravelS\Components\Apollo\Client;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class MyPortal extends \Hhxsv5\LaravelS\Console\Portal
{
    public function __construct($basePath)
    {
        parent::__construct($basePath);
    }

    protected function configure()
    {
        $this->setDescription('LaravelS console tool');
        $this->setHelp('LaravelS console tool');

        $this->addArgument('action', InputArgument::OPTIONAL, 'start|stop|restart|reload|info|help', 'help');
        $this->addOption('services', 's', InputOption::VALUE_OPTIONAL, 'The type of services to start (websocket, tcp, http, lobby, gs)'); // 新增 serverType 參數
        $this->addOption('env', 'e', InputOption::VALUE_OPTIONAL, 'The environment the command should run under, this feature requires Laravel 5.2+');
        $this->addOption('daemonize', 'd', InputOption::VALUE_NONE, 'Run as a daemon');
        $this->addOption('ignore', 'i', InputOption::VALUE_NONE, 'Ignore checking PID file of Master process');
        $this->addOption('x-version', 'x', InputOption::VALUE_OPTIONAL, 'The version(branch) of the current project, stored in $_ENV/$_SERVER');
        Client::attachCommandOptions($this);
    }

    public function start()
    {
        if (!extension_loaded('swoole') && !extension_loaded('openswoole')) {
            $this->error('LaravelS requires swoole / openswoole extension, try to `pecl install swoole` and `php --ri swoole` OR `pecl install openswoole` and `php --ri openswoole`.');
            return 1;
        }

        // Generate conf file storage/laravels.conf
        $options = $this->input->getOptions();
        if (isset($options['env']) && $options['env'] !== '') {
            $_SERVER['_ENV'] = $_ENV['_ENV'] = $options['env'];
        }
        if (isset($options['x-version']) && $options['x-version'] !== '') {
            $_SERVER['X_VERSION'] = $_ENV['X_VERSION'] = $options['x-version'];
        }
        if (!isset($options['services'])) {
            $this->error('please define services type using the --services option.');
            return 1;
        }

        // Load Apollo configurations to .env file
        if (!empty($options['enable-apollo'])) {
            $this->loadApollo($options);
        }

        // Here we go...
        $config = $this->getConfig();

        $service = $options['services'];
        $port = $config['server']['services'][$options['services']]['port'];
        $statusCode = self::runArtisanCommand($this->basePath, trim('app:show-info')." --s=$service --p=$port");
        if ($statusCode !== 0) {
            return $statusCode;
        }

        if (!$config['server']['ignore_check_pid'] && file_exists($config['server']['swoole']['pid_file'])) {
            $pid = (int)file_get_contents($config['server']['swoole']['pid_file']);
            if ($pid > 0 && self::kill($pid, 0)) {
                $this->warning(sprintf('Swoole[PID=%d] is already running.', $pid));
                return 1;
            }
        }

        if (!isset($config['server']['services'][$service])) {
            $this->error('Invalid server type. Please specify the type of server to start');
            return 1;
        }

        if ($config['server']['swoole']['daemonize']) {
            $this->trace('Swoole is running in daemon mode, see "ps -ef|grep laravels".');
        } else {
            $this->trace('Swoole is running, press Ctrl+C to quit.');
        }

        // 先直接更新port, 後續再調整其他方式
        $config['server']['listen_port'] = $port;

        (new \Hhxsv5\LaravelS\LaravelS($config['server'], $config['laravel']))->run();

        return 0;
    }

    public function showInfo()
    {
        // // 手動啟動 Laravel 應用
        // $app = require_once __DIR__ . '/../bootstrap/app.php';
        // $app->make(Kernel::class)->bootstrap();

        // // 現在可以安全使用 app() 函數
        // $laravelVersion = app()->version();

        return self::runArtisanCommand($this->basePath, 'app:show-info');

        return $output;
    }

    public function loadsomethine()
    {
        // 檢查應用是否已經加載，如果沒有則加載
        // if (! function_exists('config')) {
        //     $app = require_once __DIR__ . '/../bootstrap/app.php';
        //     $app->make(ConsoleKernel::class)->bootstrap();
        // }
        // $config = $this->getConfig();

        // $services = config('laravels.services');
        // $services = $config['server']['services'];
        // $protocols = [];

        // foreach ($services as $service => $config) {
        //     $port = $config['port'] ?? '未設置';
        //     $protocols[] = [
        //         'Service' => $service,
        //         'Protocol' => 'HTTP',
        //         'Status' => 'On',
        //         'Handler' => 'Laravel Router',
        //         'Listen At' => "http://127.0.0.1:$port",
        //     ];
        // }

        // // 使用 SymfonyStyle 格式化輸出
        // $style = new SymfonyStyle($this->input, $this->output);
        // $style->table(
        //     ['Service', 'Protocol', 'Status', 'Handler', 'Listen At'],
        //     $protocols
        // );
    }
}
