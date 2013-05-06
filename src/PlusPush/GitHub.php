<?php

namespace PlusPush;

use PlusPush\GitHub\PullRequest;
use Github\Client;

class GitHub
{
    private $client;

    private $username;
    private $repository;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function authenticate($username, $password)
    {
        $this->client->getHttpClient()->setHeaders(
            array(
                'User-Agent' => 'christofdamian/PlusPush',
            )
        );

        $this->client->authenticate(
            $username,
            $password,
            Client::AUTH_HTTP_PASSWORD
        );
    }

    public function setRepository($username, $repository)
    {
        $this->username = $username;
        $this->repository = $repository;
    }

    public function getPullRequests()
    {
        $data = $this->client->api('pull_request')->all(
            $this->username,
            $this->repository,
            'open'
        );

        $result = array();

        foreach ($data as $row) {
            $title = $row['title'];
            $number = $row['number'];
            $sha = $row['head']['sha'];

            $pullRequest = new PullRequest();
            $pullRequest->number = $number;
            $pullRequest->title = $title;
            $pullRequest->comments = $this->getComments($number);
            $pullRequest->statuses = $this->getStatuses($sha);

            $result[] = $pullRequest;
        }

        return $result;
    }

    public function getComments($number)
    {
        $comments = $this->client->api('issues')->comments()->all(
            $this->username,
            $this->repository,
            $number
        );

        $result = array();
        foreach ($comments as $comment) {
            $result[] = $comment['body'];
        }
        return $result;
    }

    public function getStatuses($sha)
    {
        return $this->client->api('repos')->statuses()->show(
            $this->username,
            $this->repository,
            $sha
        );
    }
}
