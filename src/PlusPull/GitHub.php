<?php

namespace PlusPull;

use PlusPull\GitHub\Comment;

use PlusPull\GitHub\PullRequest;
use Github\Client;

class GitHub
{
    private $client;

    private $username;
    private $repository;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->client->getHttpClient()->setHeaders(
            array(
                'User-Agent' => 'christofdamian/plus-pull',
            )
        );
    }

    public function authenticate($username, $password)
    {
        $this->client->authenticate(
            $username,
            $password,
            Client::AUTH_HTTP_PASSWORD
        );
    }

    public function authenticateWithToken($token)
    {
        $this->client->authenticate(
            $token,
            null,
            Client::AUTH_HTTP_TOKEN
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
            $number = $row['number'];

            $full = $this->client->api('pull_request')->show(
                $this->username,
                $this->repository,
                $number
            );

            $pullRequest = new PullRequest();
            $pullRequest->number = $number;
            $pullRequest->title = $row['title'];
            $pullRequest->comments = $this->getComments($number);
            $pullRequest->statuses = $this->getStatuses($row['head']['sha']);
            $pullRequest->isMergeable = $full['mergeable'];

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
            $result[] = new Comment(
                $comment['user']['login'],
                $comment['body']
            );
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

    public function merge($number)
    {
        $this->client->api('pull_request')->merge(
            $this->username,
            $this->repository,
            $number
        );
    }

    public function createToken($note)
    {
        $result = $this->client->api('authorizations')->create(
            array(
                'note' => $note,
                'note_url' => 'https://github.com/christofdamian/plus-pull',
            )
        );
        return $result['token'];
    }
}
