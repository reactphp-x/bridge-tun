<?php

namespace App;

use React\EventLoop\Loop;
use ReactphpX\Bridge\Business\PortToPort;

class PortToPortManage
{

    protected $mtime;
    protected $md5;

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

        // $stat = @\stat($file);
        // if ($stat !== false) {
        //     $mtime = \gmdate('D, d M Y H:i:s', $stat['mtime']) . ' GMT';
        //     if ($this->mtime === $mtime) {
        //         echo "File is not change\n";
        //         Loop::addTimer($cycle, function () use ($cycle, $file) {
        //             $this->loopFile($cycle, $file);
        //         });
        //         return;
        //     }
        //     $this->mtime = $mtime;
        // }
        $md5 = md5_file($file);
        if ($md5 !== false) {
            if ($this->md5 === $md5) {
                echo "File is not change\n";
                Loop::addTimer($cycle, function () use ($cycle, $file) {
                    $this->loopFile($cycle, $file);
                });
                return;
            }
            $this->md5 = $md5;
        }

        echo "File is change\n";

        $handle = fopen($file, "r");
        $configs = [];
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                $data = explode(' ', $line);
                if (empty($data[0]) || empty($data[1]) || empty($data[2])) {
                    continue;
                }
                $configs[] = [
                    'local_uuid' => null,
                    'local_address' => $data[0],
                    'remote_uuid' => $data[1],
                    'remote_address' => $data[2]
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

        $localAddresses = array_column($configs, 'local_address');
        // 已经在的地址
        $alleradyAddresses = [];
        foreach ($this->servers as $server) {
            $serverConfig = $this->servers[$server];
            if (!in_array($serverConfig['local_address'], $localAddresses)) {
                $server->stop();
                $this->servers->detach($server);
                echo "Stop server {$serverConfig['local_address']}\n";
            } else {
                $alleradyAddresses[] = $serverConfig['local_address'];
            }
        }

        foreach ($configs as $config) {
            if (in_array($config['local_address'], $alleradyAddresses)) {
                continue;
            }
            $_server = (new PortToPort($this->call))
                ->from($config['local_uuid'], $config['local_address'], function ($data) use ($config) {
                    // $data = str_replace("\r\nHost: 10.10.10.2:5001"."\r\n", "\r\nHost: ".$config['local_address']."\r\n", $data);
                    return $data;
                })
                ->to(
                    $config['remote_uuid'],
                    $config['remote_address'],
                    function ($data) {
                        return $data;
                    },
                    \ReactphpX\Bridge\Client::$secretKey
                )->start();
            $this->servers->attach($_server, $config);
            echo "Start server {$config['local_address']}\n";
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
