<?php
namespace PlusPull\Commands;

use Github\Client;
use PlusPull\GitHub;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractCommand extends Command
{
    protected function getGitHub()
    {
        return new GitHub(new Client());
    }

    protected function getYaml()
    {
        return new Yaml();
    }
}
