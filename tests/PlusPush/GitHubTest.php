<?php

namespace tests\PlusPush;

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
}
