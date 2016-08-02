<?php

namespace tests\PlusPull\GitHub;

use PlusPull\GitHub\Comment;

use PlusPull\GitHub\Label;

use PlusPull\GitHub\PullRequest;

class PullRequestTest extends \PHPUnit_Framework_TestCase
{
    private $pullRequest;

    public function setUp()
    {
        $this->pullRequest = new PullRequest();
        $this->pullRequest->user = 'self';
    }


    public function checkCommentsProvider()
    {
        return array(
            'blocker' => array(
                'comments' => array(
                     new Comment('usera', '[B]'),
                     new Comment('userb', '+1'),
                     new Comment('userc', '+1'),
                ),
                'whitelist' => null,
                'expected' => false,
            ),
            'self blocker' => array(
                'comments' => array(
                     new Comment('self', '[B]'),
                     new Comment('userb', '+1'),
                     new Comment('userc', '+1'),
                ),
                'whitelist' => null,
                'expected' => false,
            ),
            'weird spacing blocker' => array(
                'comments' => array(
                     new Comment('self', "\n[B]"),
                     new Comment('userb', '+1'),
                     new Comment('userc', '+1'),
                ),
                'whitelist' => null,
                'expected' => false,
            ),
            'ok' => array(
                'comments' => array(
                     new Comment('usera', ' +1'),
                     new Comment('userb', '+1'),
                ),
                'whitelist' => null,
                'expected' => true,
            ),
            'too low' => array(
                'comments' => array(
                     new Comment('usera', '+1'),
                     new Comment('userb', '+1'),
                     new Comment('userc', ' -1'),
                ),
                'whitelist' => null,
                'expected' => false,
            ),
            'ok with two comments from one user' => array(
                'comments' => array(
                     new Comment('usera', 'something'),
                     new Comment('usera', '+1'),
                     new Comment('userb', '+1'),
                ),
                'whitelist' => null,
                'expected' => true,
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
                     new Comment('userb', ':+1:'),
                ),
                'whitelist' => array('usera'),
                'expected' => false,
            ),
            'no self' => array(
                'comments' => array(
                     new Comment('self', '+1'),
                     new Comment('userb', '+1'),
                ),
                'whitelist' => array('usera', 'userb'),
                'expected' => false,
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
            $this->pullRequest->checkComments(2, $whitelist)
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
                'statuses' => array('state' => 'success'),
                'expected' => true,
            ),
            'error' => array(
                'statuses' => array('state' => 'error'),
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

    public function collectCommentLabelsProvider()
    {
        $blockedLabel = new Label('blocked', 'eb6420');
        $discussionLabel = new Label('discussion', '0000ff');
        return array(
            'no-comments' => array(
                'comments' => array(),
                'configLabels' => array(
                    array (
                        'name' => 'blocked',
                        'color' => 'eb6420',
                        'hook' => '[B]',
                        'label' => $blockedLabel,
                    ),
                ),
                'collectedLabels' => array(),
            ),
            'no-configured-labels' => array(
                'comments' => array(
                    new Comment('usera', '[B]'),
                ),
                'configLabels' => array(),
                'collectedLabels' => array(),
            ),
            'block-label' => array(
                'comments' => array(
                     new Comment('usera', '[B]'),
                ),
                'configLabels' => array(
                    array (
                        'name' => 'blocked',
                        'color' => 'eb6420',
                        'hook' => '[B]',
                        'label' => $blockedLabel,
                    ),
                ),
                'collectedLabels' => array(
                    $blockedLabel,
                ),
            ),
            'discussion-blocked' => array(
                'comments' => array(
                    new Comment('usera', 'This PR is for discussion.'),
                    new Comment('usera', '[B]'),
                ),
                'configLabels' => array(
                    array (
                        'name' => 'blocked',
                        'color' => 'eb6420',
                        'hook' => '[B]',
                        'label' => $blockedLabel,
                    ),
                    array (
                        'name' => 'discussion',
                        'color' => '0000ff',
                        'hook' => 'for discussion',
                        'label' => $discussionLabel,
                    ),
                ),
                'collectedLabels' => array(
                    $discussionLabel,
                    $blockedLabel,
                ),
            ),
            'no-discussion-but-blocked' => array(
                'comments' => array(
                    new Comment('usera', 'This PR is ~~for discussion~~.'),
                    new Comment('usera', '[B]'),
                ),
                'configLabels' => array(
                    array (
                        'name' => 'blocked',
                        'color' => 'eb6420',
                        'hook' => '[B]',
                        'label' => $blockedLabel,
                    ),
                    array (
                        'name' => 'discussion',
                        'color' => '0000ff',
                        'hook' => 'for discussion',
                        'label' => $discussionLabel,
                    ),
                ),
                'collectedLabels' => array(
                    $blockedLabel,
                ),
            ),
        );
    }

    /**
     * @dataProvider collectCommentLabelsProvider
     *
     * @param array $comments
     * @param array $configLabels
     * @param array $collectedLabels
     */
    public function testCollectCommentLabels(
        $comments,
        $configLabels,
        $collectedLabels
    ) {
        $this->pullRequest->comments = $comments;

        $this->pullRequest->collectCommentLabels($configLabels);
        $this->assertEquals(
            $collectedLabels,
            $this->pullRequest->collectedLabels
        );
    }
}
