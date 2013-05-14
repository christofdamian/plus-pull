<?php
namespace PlusPull\Commands;

use Github\Client;
use PlusPull\GitHub;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class CreateToken extends Command
{

    protected function configure()
    {
        $this->setName('create-token');
        $this->setDescription('Get Token');
        $this->addArgument(
            'config-file',
            InputArgument::OPTIONAL,
            'Path of the yaml configuration file',
            'config.yml'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getYaml()->parse($input->getArgument('config-file'));

        if (!is_array($config) || empty($config)) {
            throw new \InvalidArgumentException('Empty or missing config file');
        }

        $github = $this->getGitHub();

        $github->authenticate(
            $config['authorization']['username'],
            $config['authorization']['password']
        );

        var_dump($github->createToken());
    }

    protected function getGitHub()
    {
        return new GitHub(new Client());
    }

    protected function getYaml()
    {
        return new Yaml();
    }
}
