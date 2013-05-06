<?php
namespace PlusPush\Commands;

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
        $httpClient = new HttpClient();
        $httpClient->setHeaders(array('User-Agent' => 'christofdamian/PlusPush'));
        $client = new Client($httpClient);

        $yaml = new Yaml();
        $config = $yaml->parse('config.yml');

        $client->authenticate(
            $config['authorization']['username'],
            $config['authorization']['password'],
            Client::AUTH_HTTP_PASSWORD
        );


        $username = $config['repository']['username'];
        $repository = $config['repository']['name'];
        $checkStatus = $config['repository']['status'];

        $pullRequest = new PullRequest($client);
        $result =  $pullRequest->all($username, $repository, 'open');
        foreach ($result as $pull) {
            $id = $pull['id'];
            $number = $pull['number'];
            $sha = $pull['head']['sha'];

            $output->writeln(
                sprintf(
                    '%d(%d) %s %s',
                    $number,
                    $id,
                    $pull['title'],
                    $sha
                )
            );

            $comments = new \Github\Api\Issue\Comments($client);
            foreach ($comments->all($username, $repository, $number) as $comment) {
                $output->writeln(
                    sprintf(
                        '%s %s',
                        $comment['user']['login'],
                        $comment['body']
                    )
                );
            }

            $checker = new PullRequestChecker();
            var_dump($checker->checkComments($comments->all($username, $repository, $number)));

            $statuses = new Statuses($client);
            var_dump($statuses->show($username, $repository, $sha));


        }
    }
}
