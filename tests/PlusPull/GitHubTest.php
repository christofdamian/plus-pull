<?php

namespace tests\PlusPull;

use PlusPull\GitHub\Label;

use PlusPull\GitHub\Comment;

use PlusPull\GitHub\PullRequest;

use Github\Api\Repo;
use PlusPull\GitHub;

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

        $this->client->expects($this->once())
            ->method('authenticate')
            ->with(
                $this->equalTo($username),
                $this->equalTo($password),
                $this->equalTo(\Github\Client::AUTH_HTTP_PASSWORD)
            );

        $this->github->authenticate($username, $password);
    }

    public function testAuthenticateWithToken()
    {
        $token = 'token123';
        $this->client->expects($this->once())
            ->method('authenticate')
            ->with(
                $this->equalTo($token),
                $this->equalTo(\Github\Client::AUTH_HTTP_TOKEN)
            );

        $this->github->authenticateWithToken($token);
    }

    public function testGetPullRequests()
    {
        $sha = 'sha123';

        $tmp = new PullRequest();
        $tmp->title = 'test title';
        $tmp->number = 123;
        $tmp->comments = array('comments');
        $tmp->statuses = array('statuses');
        $tmp->labels = array('labels');
        $tmp->isMergeable = true;
        $tmp->user = 'test';
        $tmp->updatedAt = '2017-06-30T15:00:23Z';
        $tmp->sha = $sha;

        $pullRequestData = array(
            array(
                'title' => $tmp->title,
                'number' => $tmp->number,
                'updated_at' => $tmp->updatedAt,
                'head' => array(
                    'sha' => $sha,
                ),
                'user' => array(
                    'login' => $tmp->user,
                ),
            ),
        );
        $pullRequestFull = array( 'mergeable' => true );

        $expected = array($tmp);

        $pullRequest = $this->getMockBuilder('Github\Api\PullRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $pullRequest->expects($this->once())
            ->method('all')
            ->with(
                $this->equalTo(self::GITHUP_USERNAME),
                $this->equalTo(self::GITHUB_REPOSITORY),
                $this->equalTo(array('state' => 'open'))
            )
            ->will($this->returnValue($pullRequestData));
        $pullRequest->expects($this->once())
            ->method('show')
            ->with(
                $this->equalTo(self::GITHUP_USERNAME),
                $this->equalTo(self::GITHUB_REPOSITORY),
                $this->equalTo($tmp->number)
            )
            ->will($this->returnValue($pullRequestFull));

        $this->client->expects($this->atLeastOnce())
            ->method('api')
            ->with($this->equalTo('pull_request'))
            ->will($this->returnValue($pullRequest));

        $github = $this->getMockBuilder('PlusPull\GitHub')
            ->setConstructorArgs(array($this->client))
            ->setMethods(array('getComments', 'getStatuses', 'getLabels'))
            ->getMock();
        $github->expects($this->once())
            ->method('getComments')
            ->with($this->equalTo($tmp->number))
            ->will($this->returnValue($tmp->comments));
        $github->expects($this->once())
            ->method('getStatuses')
            ->with($this->equalTo($sha))
            ->will($this->returnValue($tmp->statuses));
        $github->expects($this->once())
            ->method('getLabels')
            ->with($this->equalTo($tmp->number))
            ->will($this->returnValue($tmp->labels));

        $github->setRepository(self::GITHUP_USERNAME, self::GITHUB_REPOSITORY);

        $this->assertEquals($expected, $github->getPullRequests());
    }

    public function testGetRepositoryLabels()
    {
        $labelsData = array(
            array(
                'name' => 'blocked',
                'color' => 'eb6420',
            ),
            array(
                'name' => 'enhancement',
                'color' => '84b6eb',
            ),
        );
        $labelsResult = array();
        $expected = array();
        foreach ($labelsData as $labelData) {
            array_push(
                $labelsResult,
                array(
                    'name' => $labelData['name'],
                    'color' => $labelData['color'],
                )
            );
            array_push(
                $expected,
                new Label(
                    $labelData['name'],
                    $labelData['color']
                )
            );
        }

        $labels = $this->getMockBuilder('Github\Api\Issue\Labels')
            ->disableOriginalConstructor()
            ->getMock();
        $labels->expects($this->once())
            ->method('all')
            ->with(
                $this->equalTo(self::GITHUP_USERNAME),
                $this->equalTo(self::GITHUB_REPOSITORY)
            )
            ->will($this->returnValue($labelsResult));

        $issue = $this->getMockBuilder('Github\Api\Issue')
            ->disableOriginalConstructor()
            ->getMock();
        $issue->expects($this->once())
            ->method('labels')
            ->will($this->returnValue($labels));

        $this->client->expects($this->once())
            ->method('api')
            ->with($this->equalTo('issues'))
            ->will($this->returnValue($issue));


        $this->assertEquals($expected, $this->github->getRepositoryLabels());
    }

    public function testCheckRepositoryLabelExists()
    {
        $labelsData = array(
            array(
                'name' => 'blocked',
                'color' => 'eb6420',
            ),
            array(
                'name' => 'enhancement',
                'color' => '84b6eb',
            ),
        );
        $labelsResult = array();
        foreach ($labelsData as $labelData) {
            array_push(
                $labelsResult,
                array(
                    'name' => $labelData['name'],
                    'color' => $labelData['color'],
                )
            );
        }
        $blocked = new Label('blocked', 'eb6420');
        $information = new Label('information', '6420eb');

        $labels = $this->getMockBuilder('Github\Api\Issue\Labels')
            ->disableOriginalConstructor()
            ->getMock();
        $labels->expects($this->exactly(2))
            ->method('all')
            ->with(
                $this->equalTo(self::GITHUP_USERNAME),
                $this->equalTo(self::GITHUB_REPOSITORY)
            )
            ->will($this->returnValue($labelsResult));

        $issue = $this->getMockBuilder('Github\Api\Issue')
            ->disableOriginalConstructor()
            ->getMock();
        $issue->expects($this->exactly(2))
            ->method('labels')
            ->will($this->returnValue($labels));

        $this->client->expects($this->exactly(2))
            ->method('api')
            ->with($this->equalTo('issues'))
            ->will($this->returnValue($issue));


        $this->assertFalse(
            $this->github->checkRepositoryLabelExists($information)
        );
        $this->assertTrue($this->github->checkRepositoryLabelExists($blocked));
    }

    public function testAddRepositoryLabel()
    {
        $labelToAdd = array(
            'name' => 'blocked',
            'color' => 'eb6420',
        );
        $expected = new Label('blocked', 'eb6420');

        $labels = $this->getMockBuilder('Github\Api\Issue\Labels')
            ->disableOriginalConstructor()
            ->getMock();
        $labels->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo(self::GITHUP_USERNAME),
                $this->equalTo(self::GITHUB_REPOSITORY),
                $labelToAdd
            )
            ->will($this->returnValue($labelToAdd));

        $issue = $this->getMockBuilder('Github\Api\Issue')
            ->disableOriginalConstructor()
            ->getMock();
        $issue->expects($this->once())
            ->method('labels')
            ->will($this->returnValue($labels));

        $this->client->expects($this->once())
            ->method('api')
            ->with($this->equalTo('issues'))
            ->will($this->returnValue($issue));


        $this->assertEquals(
            $expected,
            $this->github->addRepositoryLabel($expected)
        );
    }

    public function testGetLabels()
    {
        $number = '123';
        $labelName = 'blocked';
        $labelColor = 'eb6420';
        $labelsResult = array(
            array(
                'name' => $labelName,
                'color' => $labelColor,
            ),
        );
        $expected = array(
            new Label($labelName, $labelColor),
        );

        $labels = $this->getMockBuilder('Github\Api\Issue\Labels')
            ->disableOriginalConstructor()
            ->getMock();
        $labels->expects($this->once())
            ->method('all')
            ->with(
                $this->equalTo(self::GITHUP_USERNAME),
                $this->equalTo(self::GITHUB_REPOSITORY),
                $this->equalTo($number)
            )
            ->will($this->returnValue($labelsResult));

        $issue = $this->getMockBuilder('Github\Api\Issue')
            ->disableOriginalConstructor()
            ->getMock();
        $issue->expects($this->once())
            ->method('labels')
            ->will($this->returnValue($labels));

        $this->client->expects($this->once())
            ->method('api')
            ->with($this->equalTo('issues'))
            ->will($this->returnValue($issue));


        $this->assertEquals($expected, $this->github->getLabels($number));
    }

    public function testAddLabel()
    {
        $number = '123';
        $labelName = 'blocked';
        $labelColor = 'eb6420';
        $labelAdded = array(
            'name' => $labelName,
            'color' => $labelColor,
        );
        $labelToAdd = new Label($labelName, $labelColor);

        $labels = $this->getMockBuilder('Github\Api\Issue\Labels')
            ->disableOriginalConstructor()
            ->getMock();
        $labels->expects($this->once())
            ->method('add')
            ->with(
                $this->equalTo(self::GITHUP_USERNAME),
                $this->equalTo(self::GITHUB_REPOSITORY),
                $number,
                $labelName
            )
            ->will($this->returnValue($labelAdded));

        $issue = $this->getMockBuilder('Github\Api\Issue')
            ->disableOriginalConstructor()
            ->getMock();
        $issue->expects($this->once())
            ->method('labels')
            ->will($this->returnValue($labels));

        $this->client->expects($this->once())
            ->method('api')
            ->with($this->equalTo('issues'))
            ->will($this->returnValue($issue));


        $this->github->addLabel($number, $labelToAdd);
    }

    public function testRemoveLabel()
    {
        $number = '123';
        $labelName = 'blocked';
        $labelColor = 'eb6420';
        $labelToRemove = new Label($labelName, $labelColor);

        $labels = $this->getMockBuilder('Github\Api\Issue\Labels')
            ->disableOriginalConstructor()
            ->getMock();
        $labels->expects($this->once())
            ->method('remove')
            ->with(
                $this->equalTo(self::GITHUP_USERNAME),
                $this->equalTo(self::GITHUB_REPOSITORY),
                $number,
                $labelName
            )
            ->will($this->returnValue(null));

        $issue = $this->getMockBuilder('Github\Api\Issue')
            ->disableOriginalConstructor()
            ->getMock();
        $issue->expects($this->once())
            ->method('labels')
            ->will($this->returnValue($labels));

        $this->client->expects($this->once())
            ->method('api')
            ->with($this->equalTo('issues'))
            ->will($this->returnValue($issue));


        $this->assertEquals(
            null,
            $this->github->removeLabel($number, $labelToRemove)
        );
    }

    public function testGetComments()
    {
        $number = '123';
        $commentLogin = 'usera';
        $commentBody = 'comment';
        $commentsResult = array(
            array(
                'body' => $commentBody,
                'user' => array(
                    'login' => $commentLogin,
                ),
            ),
        );
        $reviewCommentLogin = 'userb';
        $reviewCommentBody = 'review comment';
        $reviewCommentsResult = array(
            array(
                'body' => $reviewCommentBody,
                'user' => array(
                    'login' => $reviewCommentLogin,
                ),
            ),
        );
        $expected = array(
            new Comment($commentLogin, $commentBody),
            new Comment($reviewCommentLogin, $reviewCommentBody),
        );

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

        $this->client->expects($this->at(0))
            ->method('api')
            ->with($this->equalTo('issues'))
            ->will($this->returnValue($issue));

        $reviewComments = $this->getMockBuilder('Github\Api\Issue\Comments')
            ->disableOriginalConstructor()
            ->getMock();
        $reviewComments->expects($this->once())
            ->method('all')
            ->with(
                $this->equalTo(self::GITHUP_USERNAME),
                $this->equalTo(self::GITHUB_REPOSITORY),
                $this->equalTo($number)
            )
            ->will($this->returnValue($reviewCommentsResult));

        $pullRequest = $this->getMockBuilder('Github\Api\PullRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $pullRequest->expects($this->once())
            ->method('comments')
            ->will($this->returnValue($reviewComments));

        $this->client->expects($this->at(1))
            ->method('api')
            ->with($this->equalTo('pull_request'))
            ->will($this->returnValue($pullRequest));

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
            ->method('combined')
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
        $sha = 'sha123';

        $pullRequest = $this->getMockBuilder('Github\Api\PullRequest')
                     ->disableOriginalConstructor()
                     ->getMock();
        $pullRequest->expects($this->once())
            ->method('merge')
            ->with(
                $this->equalTo(self::GITHUP_USERNAME),
                $this->equalTo(self::GITHUB_REPOSITORY),
                $this->equalTo($number),
                $this->equalTo(''),
                $this->equalTo($sha),
                $this->equalTo('merge')
            );

        $this->client->expects($this->once())
            ->method('api')
            ->with($this->equalTo('pull_request'))
            ->will($this->returnValue($pullRequest));

        $this->github->merge($number, $sha);
    }

    public function testUpdateLabels()
    {
        $blockedLabel = new Label('blocked', 'eb6420');
        $discussionLabel = new Label('discussion', '0000ff');
        $anotherLabel = new Label('another', '0000ff');
        $configuredLabels = array();
        $configuredLabels['blocked'] = array(
            'name' => 'blocked',
            'color' => 'eb6420',
            'label' => $blockedLabel,
        );
        $configuredLabels['discussion'] = array(
            'name' => 'discussion',
            'color' => '0000ff',
            'label' => $discussionLabel,
        );

        $pullRequest = new PullRequest();
        $pullRequest->number = 123;
        $pullRequest->labels = array($blockedLabel, $anotherLabel);
        $pullRequest->collectedLabels = array($discussionLabel,);

        $github = $this->getMockBuilder('PlusPull\GitHub')
                ->setConstructorArgs(array($this->client))
                ->setMethods(array('addLabel', 'removeLabel'))
                ->getMock();
        $github->expects($this->once())
            ->method('addLabel')
            ->with(
                $this->equalTo($pullRequest->number),
                $discussionLabel
            );
        $github->expects($this->once())
            ->method('removeLabel')
            ->with(
                $this->equalTo($pullRequest->number),
                $blockedLabel
            );

        $github->updateLabels($pullRequest, $configuredLabels);
    }

    public function testCreateToken()
    {
        $token = 'token123';
        $note = 'some note';

        $authorizations = $this->getMockBuilder('Github\Api\Authorizations')
            ->disableOriginalConstructor()
            ->getMock();
        $authorizations->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo(
                    array(
                        'note' => $note,
                        'note_url' => GitHub::NOTE_URL,
                        'scopes' => array('repo'),
                    )
                )
            )
            ->will($this->returnValue(array('token' => $token)));

        $this->client->expects($this->once())
            ->method('api')
            ->with($this->equalTo('authorizations'))
            ->will($this->returnValue($authorizations));

        $this->assertEquals($token, $this->github->createToken($note));
    }
}
