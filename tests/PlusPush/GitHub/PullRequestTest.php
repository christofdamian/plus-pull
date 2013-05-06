<?php

namespace tests\PlusPush\GitHub;

use PlusPush\GitHub\PullRequest;

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
                     '[B]',
                ),
                'expected' => false,
            ),
            'ok' => array(
                'comments' => array(
                     '+1',
                     '+1',
                ),
                'expected' => true,
            ),
            'too low' => array(
                'comments' => array(
                     '+1',
                     '+1',
                     '-1',
                ),
                'expected' => false,
            ),
        );
    }


    /**
     * @dataProvider checkCommentsProvider
     *
     * @param array $comments
     * @param boolean $expected
     */
    public function testCheckComments($comments, $expected)
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
}
