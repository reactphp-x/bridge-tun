<?php

namespace App;

use ReactphpX\Bridge\Interface\VerifyInterface;
use ReactphpX\Bridge\Server as ServerBridge;
use ReactphpX\Bridge\Pool;
use ReactphpX\Bridge\Http\HttpBridge;
use ReactphpX\Bridge\WebSocket\WsBridge;
use ReactphpX\Bridge\Tcp\TcpBridge;
use ReactphpX\Bridge\BridgeStrategy;
use ReactphpX\Bridge\Io\Tcp;

class Server
{

    protected $serverBridge;

    public function __construct(VerifyInterface $verify)
    {
        $this->serverBridge = new ServerBridge($verify);
        $this->serverBridge->enableKeepAlive(40);
    }

    public function listen($port)
    {
        $pool = new Pool($this->serverBridge, [
            'max_connections' => 40,
            'connection_timeout' => 2,
            'uuid_max_tunnel' => 1,
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
