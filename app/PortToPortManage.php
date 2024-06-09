<?php

namespace App;

use React\EventLoop\Loop;
use Reactphp\Framework\Bridge\Business\PortToPort;

class PortToPortManage
{

    protected $mtime;

    protected $configs = [];

    protected $servers;

    protected $call;

    public function __construct($call)
    {
        $this->call = $call;
        $this->servers = new \SplObjectStorage;
    }


    public function loopFile($cycle = 10, $file)
    {

        if (!file_exists($file)) {
            Loop::addTimer($cycle, function () use ($cycle, $file) {
                $this->loopFile($cycle, $file);
            });
            return;
        }

        $stat = @\stat($file);
        if ($stat !== false) {
            $mtime = \gmdate('D, d M Y H:i:s', $stat['mtime']) . ' GMT';
            if ($this->mtime === $mtime) {
                Loop::addTimer($cycle, function () use ($cycle, $file) {
                    $this->loopFile($cycle, $file);
                });
                return;
            }
            $this->mtime = $mtime;
        }

        $handle = fopen($file, "r");
        $configs = [];
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                $data = explode(' ', $line);
                if (empty($data[0]) || empty($data[1]) || empty($data[2]) || empty($data[3])) {
                    continue;
                }
                $configs[] = [
                    'local_uuid' => $data[0],
                    'local_address' => $data[1],
                    'remote_uuid' => $data[2],
                    'remote_address' => $data[3]
                ];
            }
            fclose($handle);
        }
        echo "Update PortToPortConfig\n";
        echo var_export($configs) . "\n";
        $this->start($configs);
        Loop::addTimer($cycle, function () use ($cycle, $file) {
            $this->loopFile($cycle, $file);
        });
    }

    public function start($configs)
    {
        if (!$this->servers) {
            $this->servers = new \SplObjectStorage;
        }

        $localAddresses = array_column($configs, 'local_address');
        // 已经在的地址
        $alleradyAddresses = [];
        foreach ($this->servers as $server) {
            $serverConfig = $this->servers[$server];
            if (!in_array($serverConfig['local_address'], $localAddresses)) {
                $server->stop();
                $this->servers->detach($server);
            } else {
                $alleradyAddresses[] = $serverConfig['local_address'];
            }
        }

        foreach ($configs as $config) {
            if (in_array($config['local_address'], $alleradyAddresses)) {
                continue;
            }
            $_server = (new PortToPort($this->call))
                ->from($config['local_uuid'], $config['local_address'], function ($data) {
                    return $data;
                })
                ->to(
                    $config['remote_uuid'],
                    $config['remote_address'],
                    function ($data) {
                        return $data;
                    }
                )->start();
            $this->servers->attach($_server, $config);
        }
        $this->configs = $configs;
        return $this;
    }

    public function stop()
    {

        foreach ($this->servers as $server) {
            $server->stop();
            $this->servers->detach($server);
        }
    }

    public function __destruct()
    {
        $this->stop();
    }
}
