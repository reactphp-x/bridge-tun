<?php

namespace App;

use Reactphp\Framework\Bridge\Verify\VerifyUuid as BaseVerifyUuid;
use React\EventLoop\Loop;

class VerifyUuid extends BaseVerifyUuid
{
    public function update($uuidToSomething)
    {
        parent::__construct($uuidToSomething);
    }

    public function loopFile($cycle = 10)
    {
   
        $file = getcwd() . '/tun.txt';
        if (!file_exists($file)) {
            return;
        }
        $uuidToSomething = [];
        $handle = fopen($file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                $data = explode(' ', $line);
                $uuidToSomething[$data[0]] = $data[1];
            }
            fclose($handle);
        }
        echo "Update uuidToSomething\n";
        echo var_export($uuidToSomething) . "\n";
        $this->update($uuidToSomething);

        Loop::addTimer($cycle, function () use ($cycle) {
            $this->loopFile($cycle);
        });
    }
}