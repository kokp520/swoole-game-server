<?php

namespace App;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Hhxsv5\LaravelS\Components\Apollo\Client;

class MyPortal extends \Hhxsv5\LaravelS\Console\Portal
{
    public function __construct($basePath)
    {
        parent::__construct($basePath);
    }

    private function delAlreadyUsedProcess($port, $pidfile)
    {
        $cmd = "lsof -i :$port -t";
        $pid = shell_exec($cmd);

        if ($pid) {
            $pidList = explode("\n", trim($pid));
            foreach ($pidList as $pid) {
                // echo "Killing process with PID $pid on port $port" . PHP_EOL;
                exec("kill -9 $pid");
            }
            // var_dump($pidfile);
            `sleep 0.1`;
        }
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
        $statusCode = self::runArtisanCommand($this->basePath, trim('app:show-info') . " --s=$service --p=$port");
        if ($statusCode !== 0) {
            return $statusCode;
        }

        $pidfile = $config['server']['services'][$service]['pid_file'];
        $this->delAlreadyUsedProcess($port, $pidfile);

        if (!$config['server']['ignore_check_pid'] && file_exists($pidfile)) {
            $pid = (int)file_get_contents($pidfile);
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
        return self::runArtisanCommand($this->basePath, 'app:show-info');
    }
}
