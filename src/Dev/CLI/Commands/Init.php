<?php

namespace App\XQL\Dev\CLI\Commands;

use \App\XQL\Dev\CLI\Command;

class Init extends Command
{
    protected function handle(array $args, array $params, array $flags): bool
    {

        $dir = realpath(".");
        if(array_key_exists("dir", $params)) {
            $dir = $params['dir'];
        }

        $cwd = dirname(__FILE__);
        $projectDir = preg_replace("/(([\\\\\/])(Dev)([\\\\\/])).*/", "", $cwd);
        $binDir = $projectDir . "/bin";
        $windsorSource = $binDir . $this->separator() . "windsor";
        $windsorTarget = $dir . $this->separator() . "windsor";
        copy($windsorSource, $windsorTarget);

        return true;
    }
}