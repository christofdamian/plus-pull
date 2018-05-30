<?php
namespace PlusPull\Commands;

use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $question = new Question('GitHub Username?');
        $username = $helper->ask($input, $output, $question);

        $question = new Question('GitHub Password?');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $question);

        $github = $this->getGitHub();
        $github->authenticate($username, $password);
        $token = $github->createToken($input->getOption('note'));

        $config = array(
            'authorization' => array(
                 'token' => $token,
             ),
        );
        $yaml = $this->dumpYaml($config);

        $output->writeln("\nAdd the following code to your config file\n");
        $output->writeln("<info>$yaml</info>");
    }

    protected function dumpYaml($config)
    {
        return $this->getYaml()->dump($config);
    }
}
