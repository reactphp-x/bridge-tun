<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\VerifyUuid;
use Ramsey\Uuid\Uuid;

$type = $argv[1] ?? '';

if ($type == '-s') {
    $port = $argv[2] ?? '';
    if (empty($port)) {
        echo "Usage: php index.php -s <port>\n";
        exit(1);
    }
    $verify = new VerifyUuid([]);
    $verify->loopFile(10);
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
    $client = new \App\Client($uri, $uuid);
    $client->start();
} 
else if ($type == '-u') {
    for ($i = 0; $i < 10; $i++) {
        $uuid = Uuid::uuid4();
        echo $uuid->toString() . "\n";
    }
}
else {
    echo "Usage: php index.php -s|-c\n";
    exit(1);
}
