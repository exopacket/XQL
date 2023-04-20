<?php

namespace XQL\Dev\CLI;

use XQL\Dev\CLI\Commands\Init;
use Minicli\App;
use Minicli\Command\CommandCall;

class CommandRegistry
{

    private string $projectDir;

    protected function map(): array {
        return [
            'init' => Init::class
        ];
    }

    public function __construct(App $app, CommandCall $call, string $projectDir)
    {
        $this->projectDir = $projectDir;
        foreach($this->map() as $command => $class) {
            $this->register($command, $class, $app, $call);
        }
    }

    public static function createApp($argv, $projectDir)
    {
        $app = new App();
        $input = new CommandCall($argv);
        new self($app, $input, $projectDir);
        $app->runCommand($input->getRawArgs());
    }

    private function register(string $command, string $class, App $app, CommandCall $call) {
        $dir = $this->projectDir;
        $app->registerCommand(strtolower($command), function () use ($class, $app, $call, $dir) {
            $command = new $class($app, $call, $dir);
        });
    }

}