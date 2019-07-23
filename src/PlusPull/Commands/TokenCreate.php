<?php
namespace PlusPull\Commands;

use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class TokenCreate extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('token:create');
        $this->setDescription('Get Token');
        $this->addOption(
            'note',
            null,
            InputOption::VALUE_REQUIRED,
            'Note for the authorization token on github',
            'plus-push'
        );
    }

    protected function yamlDump($config)
    {
        $this->getYaml()->dump($config);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelper('dialog');

        $output->writeln("Please enter your github credentials");
        $username = $dialog->ask($output, 'username: ');
        $password = $dialog->askHiddenResponse($output, 'password: ');

        $github = $this->getGitHub();
        $github->authenticate($username, $password);
        $token = $github->createToken($input->getOption('note'));

        $config = array(
            'authorization' => array(
                 'token' => $token,
             ),
        );
        $yaml = $this->yamlDump($config);

        $output->writeln("\nAdd the following code to your config file\n");
        $output->writeln("<info>$yaml</info>");
    }
}
