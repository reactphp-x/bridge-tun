<?php

namespace App;

use ReactphpX\Bridge\Verify\VerifyUuid as BaseVerifyUuid;
use React\EventLoop\Loop;

class VerifyUuid extends BaseVerifyUuid
{

    protected $mtime;

    public function update($uuidToSomething)
    {
        parent::__construct($uuidToSomething);
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

        Loop::addTimer($cycle, function () use ($cycle, $file) {
            $this->loopFile($cycle, $file);
        });
    }
}
