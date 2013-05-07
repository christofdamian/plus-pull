<?php
namespace PlusPush\Commands;

use PlusPush\GitHub;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Github\Client;
use Github\Api\PullRequest;
use Github\HttpClient\HttpClient;
use Github\Api\PullRequest\Comments;
use Github\Api\Repository\Statuses;
use Symfony\Component\Yaml\Yaml;
use PlusPush\PullRequestChecker;

class Show extends Command
{

    protected function configure()
    {
        $this->setName('show');
        $this->setDescription('Show comments');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getYaml()->parse('config.yml');

        if (!is_array($config) || empty($config)) {
            throw new \InvalidArgumentException('Empty or missing config file');
        }

        $github = $this->getGitHub();

        $github->authenticate(
            $config['authorization']['username'],
            $config['authorization']['password']
        );

        $username = $config['repository']['username'];
        $repository = $config['repository']['name'];
        $checkStatus = !empty($config['repository']['status']);

        $github->setRepository($username, $repository);

        foreach ($github->getPullRequests() as $pullRequest) {
            $output->write($pullRequest->number.' '.$pullRequest->title);

            if ($pullRequest->checkComments()) {
                $output->write(' OK');
            }

            $output->writeln('');
        }
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
