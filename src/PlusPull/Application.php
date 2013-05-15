<?php
namespace PlusPull;

use PlusPull\Commands\Check;
use PlusPull\Commands\TokenCreate;
use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new Check();
        $defaultCommands[] = new TokenCreate();

        return $defaultCommands;
    }
}
