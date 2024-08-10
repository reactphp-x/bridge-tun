<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\VerifyUuid;
use Ramsey\Uuid\Uuid;
use ReactphpX\Bridge\Client;
use ReactphpX\Bridge\Server;


// Client::$debug = true;
// Server::$debug = true;


$type = $argv[1] ?? '';

if ($type == '-s') {
    $port = $argv[2] ?? '';
    if (empty($port)) {
        echo "Usage: php index.php -s <port>\n";
        exit(1);
    }

    $file = $argv[3] ?? '';
    if (empty($file)) {
        echo "Usage: php index.php -s <port> <file>\n";
        exit(1);
    }

    if (!file_exists($file)) {
        echo "File not found $path \n";
        exit(1);
    }

    $verify = new VerifyUuid([]);
    $verify->loopFile(10, $file);
    $server = new \App\Server($verify);
    $pool = $server->listen($port);
    echo "Server running at tcp://0.0.0.0:$port\n";
    echo "Server running at ws://0.0.0.0:$port\n";
    echo "Press Ctrl+C to quit\n";
} else if ($type == '-c') {
    $uri = $argv[2] ?? '';
    if (empty($uri)) {
        echo "Usage: php index.php -c <uri>\n";
        exit(1);
    }
    $uuid = $argv[3] ?? '';
    if (empty($uuid)) {
        echo "Usage: php index.php -c <uri> <uuid>\n";
        exit(1);
    }
    if (isset($argv[4])) {
        Client::$secretKey = $argv[4];
    } else {
        trigger_error("Secret key not set", E_USER_WARNING);
        echo "Usage: php index.php -c <uri> <uuid> <secret_key>\n";
    }
    $client = new \App\Client($uri, $uuid);
    $call = $client->start();

    $file = $argv[5] ?? '';
    if ($file && !file_exists($file)) {
        echo "Usage: php index.php -c <uri> <uuid> <secret_key> <file>\n";
        exit(1);
    } else {
        if ($file) {
            $portToPortManage = new \App\PortToPortManage($call);
            $portToPortManage->loopFile(5, $file);
        }
    }


} else if ($type == '-u') {
    if (file_exists('./tun.txt')) {
        echo "File tun.txt already exists\n";
        exit(1);
    }
    for ($i = 0; $i < 10; $i++) {
        $uuid = Uuid::uuid4();
        $p = $i + 1;
        $line = $uuid->toString() . " 10.10.10.{$p}\n";
        file_put_contents('./tun.txt', $line, FILE_APPEND);
    }
} else {
    echo "Usage: php index.php -s|-c\n";
    exit(1);
}
