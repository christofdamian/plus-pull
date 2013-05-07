<?php

namespace tests\PlusPush;

use Github\Api\Repo;
use PlusPush\GitHub;

class GitHubTests extends \PHPUnit_Framework_TestCase
{
    private $client;

    private $github;

    public function setUp()
    {
        $this->client = $this->getMockBuilder('Github\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->github = new GitHub($this->client);
    }

    public function testAuthenticate()
    {
        $username = 'username';
        $password = 'password';

        $httpClient = $this->getMock('Github\HttpClient\HttpClient');
        $httpClient->expects($this->once())
            ->method('setHeaders');

        $this->client->expects($this->once())
            ->method('getHttpClient')
            ->will($this->returnValue($httpClient));

        $this->client->expects($this->once())
            ->method('authenticate')
            ->with(
                $this->equalTo($username),
                $this->equalTo($password),
                $this->equalTo(\Github\Client::AUTH_HTTP_PASSWORD)
            );

        $this->github->authenticate($username, $password);
    }

    public function testGetStatuses()
    {
        $sha = 'sha123';
        $statusesResult = array('statuses');
        $username = 'testuser';
        $repository = 'test-repsitory';

        $statuses = $this->getMockBuilder('Github\Api\Repository\Statuses')
            ->disableOriginalConstructor()
            ->getMock();
        $statuses->expects($this->once())
            ->method('show')
            ->with(
                $this->equalTo($username),
                $this->equalTo($repository),
                $this->equalTo($sha)
            )
            ->will($this->returnValue($statusesResult));

        $repo = $this->getMockBuilder('Github\Api\Repo')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('statuses')
            ->will($this->returnValue($statuses));

        $this->client->expects($this->once())
            ->method('api')
            ->with($this->equalTo('repos'))
            ->will($this->returnValue($repo));

        $this->github->setRepository($username, $repository);

        $this->assertEquals($statusesResult, $this->github->getStatuses($sha));
    }
}
