<?php

namespace tests\PlusPush;

use PlusPush\GitHub\PullRequest;

use Github\Api\Repo;
use PlusPush\GitHub;

class GitHubTests extends \PHPUnit_Framework_TestCase
{
    private $client;

    private $github;

    const GITHUP_USERNAME = 'testuser';
    const GITHUB_REPOSITORY = 'test-repository';

    public function setUp()
    {
        $this->client = $this->getMockBuilder('Github\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->github = new GitHub($this->client);
        $this->github->setRepository(
            self::GITHUP_USERNAME,
            self::GITHUB_REPOSITORY
        );
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

    public function testGetPullRequests()
    {
        $tmp = new PullRequest();
        $tmp->title = 'test title';
        $tmp->number = 123;
        $tmp->comments = array('comments');
        $tmp->statuses = array('statuses');

        $sha = 'sha123';

        $pullRequestData = array(
            array(
                'title' => $tmp->title,
                'number' => $tmp->number,
                'head' => array(
                    'sha' => $sha,
                ),
            ),
        );
        $expected = array($tmp);

        $pullRequest = $this->getMockBuilder('Github\Api\PullRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $pullRequest->expects($this->once())
            ->method('all')
            ->with(
                $this->equalTo(self::GITHUP_USERNAME),
                $this->equalTo(self::GITHUB_REPOSITORY),
                $this->equalTo('open')
            )
            ->will($this->returnValue($pullRequestData));

        $this->client->expects($this->once())
            ->method('api')
            ->with($this->equalTo('pull_request'))
            ->will($this->returnValue($pullRequest));

        $github = $this->getMockBuilder('PlusPush\GitHub')
            ->setConstructorArgs(array($this->client))
            ->setMethods(array('getComments', 'getStatuses'))
            ->getMock();
        $github->expects($this->once())
            ->method('getComments')
            ->with($this->equalTo($tmp->number))
            ->will($this->returnValue($tmp->comments));
        $github->expects($this->once())
            ->method('getStatuses')
            ->with($this->equalTo($sha))
            ->will($this->returnValue($tmp->statuses));

        $github->setRepository(self::GITHUP_USERNAME, self::GITHUB_REPOSITORY);


        $this->assertEquals($expected, $github->getPullRequests());
    }

    public function testGetComments()
    {
        $number = '123';
        $commentsResult = array(array('body' => 'comment'));
        $expected = array($commentsResult[0]['body']);

        $comments = $this->getMockBuilder('Github\Api\Issue\Comments')
            ->disableOriginalConstructor()
            ->getMock();
        $comments->expects($this->once())
            ->method('all')
            ->with(
                $this->equalTo(self::GITHUP_USERNAME),
                $this->equalTo(self::GITHUB_REPOSITORY),
                $this->equalTo($number)
            )
            ->will($this->returnValue($commentsResult));

        $issue = $this->getMockBuilder('Github\Api\Issue')
            ->disableOriginalConstructor()
            ->getMock();
        $issue->expects($this->once())
            ->method('comments')
            ->will($this->returnValue($comments));

        $this->client->expects($this->once())
            ->method('api')
            ->with($this->equalTo('issues'))
            ->will($this->returnValue($issue));


        $this->assertEquals($expected, $this->github->getComments($number));
    }

    public function testGetStatuses()
    {
        $sha = 'sha123';
        $statusesResult = array('statuses');

        $statuses = $this->getMockBuilder('Github\Api\Repository\Statuses')
            ->disableOriginalConstructor()
            ->getMock();
        $statuses->expects($this->once())
            ->method('show')
            ->with(
                $this->equalTo(self::GITHUP_USERNAME),
                $this->equalTo(self::GITHUB_REPOSITORY),
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


        $this->assertEquals($statusesResult, $this->github->getStatuses($sha));
    }

    public function testMerge()
    {
        $number = 123;

        $pullRequest = $this->getMockBuilder('Github\Api\PullRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $pullRequest->expects($this->once())
            ->method('merge')
            ->with(
                $this->equalTo(self::GITHUP_USERNAME),
                $this->equalTo(self::GITHUB_REPOSITORY),
                $this->equalTo($number)
            );

        $this->client->expects($this->once())
            ->method('api')
            ->with($this->equalTo('pull_request'))
            ->will($this->returnValue($pullRequest));

        $this->github->merge($number);
    }
}
