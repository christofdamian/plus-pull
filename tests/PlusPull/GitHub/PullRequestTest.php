<?php

namespace tests\PlusPull\GitHub;

use PlusPull\GitHub\Comment;

use PlusPull\GitHub\PullRequest;

class PullRequestTest extends \PHPUnit_Framework_TestCase
{
    private $pullRequest;

    public function setUp()
    {
        $this->pullRequest = new PullRequest();
    }


    public function checkCommentsProvider()
    {
        return array(
            'blocker' => array(
                'comments' => array(
                     new Comment('usera', '[B]'),
                ),
                'whitelist' => null,
                'expected' => false,
            ),
            'ok' => array(
                'comments' => array(
                     new Comment('usera', '+1'),
                     new Comment('userb', '+1'),
                ),
                'whitelist' => null,
                'expected' => true,
            ),
            'too low' => array(
                'comments' => array(
                     new Comment('usera', '+1'),
                     new Comment('userb', '+1'),
                     new Comment('userc', '-1'),
                ),
                'whitelist' => null,
                'expected' => false,
            ),
            'one user' => array(
                'comments' => array(
                     new Comment('usera', '+1'),
                     new Comment('usera', '+1'),
                     new Comment('usera', '+1'),
                ),
                'whitelist' => null,
                'expected' => false,
            ),
            'whitelist ok' => array(
                'comments' => array(
                     new Comment('usera', '+1'),
                     new Comment('userb', '+1'),
                ),
                'whitelist' => array('usera', 'userb'),
                'expected' => true,
            ),
            'whitelist ko' => array(
                'comments' => array(
                     new Comment('usera', '+1'),
                     new Comment('userb', '+1'),
                ),
                'whitelist' => array('usera'),
                'expected' => true,
            ),
        );
    }


    /**
     * @dataProvider checkCommentsProvider
     *
     * @param array $comments
     * @param array $whitelist
     * @param boolean $expected
     */
    public function testCheckComments($comments, $whitelist, $expected)
    {
        $this->pullRequest->comments = $comments;

        $this->assertEquals(
            $expected,
            $this->pullRequest->checkComments(2)
        );
    }

    public function isBlockerProvider()
    {
        return array(
            'blocker' => array(
                'commentBody' => '[B]',
                'isBlocker' => true,
            ),
            'not a blocker' => array(
                'commentBody' => '',
                'isBlocker' => false,
            ),
        );
    }

    /**
     * @dataProvider isBlockerProvider
     *
     * @param string $comment
     * @param boolean $isBlocker
     */
    public function testIsBlocker($commentBody, $isBlocker)
    {
        $this->assertEquals(
            $isBlocker,
            $this->pullRequest->isBlocker($commentBody)
        );
    }


    public function commentValueProvider()
    {
        return array(
            'blocker' => array(
                'commentBody' => '[B]',
                'value' => 0,
            ),
            'just text' => array(
                'commentBody' => 'just text',
                'value' => 0,
            ),
            '+1' => array(
                'commentBody' => '+1',
                'value' => 1,
            ),
            ':+1:' => array(
                'commentBody' => ':+1:',
                'value' => 1,
            ),
            '-1' => array(
                'commentBody' => '-1',
                'value' => -1,
            ),
            ':-1:' => array(
                'commentBody' => ':-1:',
                'value' => -1,
            ),
        );
    }

    /**
     * @dataProvider commentValueProvider
     *
     * @param string $commentBody
     * @param integer $value
     */
    public function testCommentValue($commentBody, $value)
    {
        $this->assertEquals(
            $value,
            $this->pullRequest->getCommentValue($commentBody)
        );
    }

    public function checkStatusesProvider()
    {
        return array(
            'empty' => array(
                'statuses' => null,
                'expected' => false,
            ),
            'success' => array(
                'statuses' => array(array('state' => 'success')),
                'expected' => true,
            ),
            'error' => array(
                'statuses' => array(array('state' => 'error')),
                'expected' => false,
            ),
        );
    }

    /**
     * @dataProvider checkStatusesProvider
     *
     * @param array $statuses
     * @param boolean $expected
     */
    public function testCheckStatuses($statuses, $expected)
    {
        $this->pullRequest->statuses = $statuses;
        $this->assertEquals($expected, $this->pullRequest->checkStatuses());
    }

    public function testIsMergeable()
    {
        $this->assertFalse($this->pullRequest->isMergeable());
    }
}
