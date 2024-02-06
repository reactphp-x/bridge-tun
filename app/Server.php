<?php

namespace App;

use Reactphp\Framework\Bridge\Interface\VerifyInterface;
use Reactphp\Framework\Bridge\Server as ServerBridge;
use Reactphp\Framework\Bridge\Pool;
use Reactphp\Framework\Bridge\Http\HttpBridge;
use Reactphp\Framework\Bridge\WebSocket\WsBridge;
use Reactphp\Framework\Bridge\Tcp\TcpBridge;
use Reactphp\Framework\Bridge\BridgeStrategy;
use Reactphp\Framework\Bridge\Io\Tcp;

class Server
{

    protected $serverBridge;

    public function __construct(VerifyInterface $verify)
    {
        $this->serverBridge = new ServerBridge($verify);
    }

    public function listen($port)
    {
        $pool = new Pool($this->serverBridge, [
            'max_connections' => 5,
            'connection_timeout' => 2,
            'keep_alive' => 30,
            'wait_timeout' => 3
        ]);
        new Tcp('0.0.0.0:' . $port, new BridgeStrategy([
            new TcpBridge($this->serverBridge),
            new HttpBridge(new WsBridge($this->serverBridge))
        ]));
        return $pool;
    }
}