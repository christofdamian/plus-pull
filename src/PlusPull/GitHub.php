<?php

namespace PlusPull;

use PlusPull\GitHub\Label;

use PlusPull\GitHub\Comment;

use PlusPull\GitHub\PullRequest;
use Github\Client;

class GitHub
{
    const NOTE_URL = 'https://github.com/christofdamian/plus-pull';
    const USER_AGENT = 'christofdamian/plus-pull';


    private $client;

    private $username;
    private $repository;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->client->getHttpClient()->setHeaders(
            array(
                'User-Agent' => self::USER_AGENT,
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
            array('state' => 'open')
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
            $pullRequest->labels = $this->getLabels($number);
            $pullRequest->isMergeable = $full['mergeable'];
            $pullRequest->user = $row['user']['login'];

            $result[] = $pullRequest;
        }

        return $result;
    }

    public function getRepositoryLabels()
    {
        $labels = $this->client->api('issues')->labels()->all(
            $this->username,
            $this->repository
        );

        $result = array();
        foreach ($labels as $label) {
            $result[] = new Label(
                $label['name'],
                $label['color']
            );
        }
        return $result;
    }

    public function checkRepositoryLabelExists($label)
    {
        $labels = $this->getRepositoryLabels();
        $result = false;
        foreach ($labels as $existingLabel) {
            $result = (strcmp($label->name, $existingLabel->name) == 0);
            if ($result) {
                break;
            }
        }
        return $result;
    }

    public function addRepositoryLabel($label)
    {
        $labelCreated = $this->client->api('issues')->labels()->create(
            $this->username,
            $this->repository,
            $label->toArray()
        );

        return new Label(
            $labelCreated['name'],
            $labelCreated['color']
        );
    }

    public function getLabels($number)
    {
        $labels = $this->client->api('issues')->labels()->all(
            $this->username,
            $this->repository,
            $number
        );

        $result = array();
        foreach ($labels as $label) {
            $result[] = new Label(
                $label['name'],
                $label['color']
            );
        }
        return $result;
    }

    public function addLabel($number, $label)
    {
        $labelAdded = $this->client->api('issues')->labels()->add(
            $this->username,
            $this->repository,
            $number,
            $label->name
        );
    }

    public function removeLabel($number, $label)
    {
        $result = $this->client->api('issues')->labels()->remove(
            $this->username,
            $this->repository,
            $number,
            $label->name
        );

        return $result;
    }

    public function getComments($number)
    {
        $comments = $this->client->api('issues')->comments()->all(
            $this->username,
            $this->repository,
            $number
        );

        $review_comments = $this->client->api('pull_request')->comments()->all(
            $this->username,
            $this->repository,
            $number
        );

        $result = array();
        foreach (array_merge($comments, $review_comments) as $comment) {
            $result[] = new Comment(
                $comment['user']['login'],
                $comment['body']
            );
        }
        return $result;
    }

    public function getStatuses($sha)
    {
        return $this->client->api('repos')->statuses()->combined(
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
            $number,
            ''
        );
    }

    public function updateLabels($pullRequest, $configuredLabels)
    {
        $labelsToAdd = array_diff(
            $pullRequest->collectedLabels,
            $pullRequest->labels
        );
        foreach ($labelsToAdd as $labelToAdd) {
            $this->addLabel(
                $pullRequest->number,
                $labelToAdd
            );
        }

        $labelsToRemove = array_diff(
            $pullRequest->labels,
            $pullRequest->collectedLabels
        );
        foreach ($labelsToRemove as $labelToRemove) {
            foreach ($configuredLabels as $configuredLabel) {
                if ($labelToRemove->name == $configuredLabel['name']) {
                    $this->removeLabel(
                        $pullRequest->number,
                        $labelToRemove
                    );
                }
            }
        }
    }

    public function createToken($note)
    {
        $result = $this->client->api('authorizations')->create(
            array(
                'note' => $note,
                'note_url' => self::NOTE_URL,
                'scopes' => array('repo'),
            )
        );
        return $result['token'];
    }
}
