<?php

namespace App\XQL\Dev\CLI;

use App\XQL\Dev\CLI\Commands\Init;
use Minicli\App;
use Minicli\Command\CommandCall;

class CommandRegistry
{

    protected function map(): array {
        return [
            'init' => Init::class
        ];
    }

    public function __construct(App $app, CommandCall $call)
    {
        foreach($this->map() as $command => $class) {
            $this->register($command, $class, $app, $call);
        }
    }

    public static function createApp($argv)
    {
        $app = new App();
        $input = new CommandCall($argv);
        $instance = new self($app, $input);
        $app->runCommand($input->getRawArgs());
    }

    private function register(string $command, string $class, App $app, CommandCall $call) {
        $app->registerCommand(strtolower($command), function () use ($class, $app, $call) {
            $command = new $class($app, $call);
        });
    }

}