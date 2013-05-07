<?php

namespace PlusPull\GitHub;

class PullRequest
{
    public $number;

    public $title;

    public $comments = array();

    public $statuses = array();

    /**
     * @var boolean
     */
    public $isMergeable;

    public function checkComments($required = 3)
    {
        $voted = array();
        $total = 0;
        foreach ($this->comments as $comment) {
            if ($this->isBlocker($comment->body)) {
                return false;
            }

            if (empty($voted[$comment->login])) {
                $total += $this->getCommentValue($comment->body);
                $voted[$comment->login] = true;
            }
        }
        return $total >= $required;
    }

    public function isBlocker($commentBody)
    {
        return preg_match('/^\[B\]/', $commentBody);
    }

    public function getCommentValue($commentBody)
    {
        if (preg_match('/^(\+1\b|:\+1:)/', $commentBody)) {
            return 1;
        }
        if (preg_match('/^(\-1\b|:\-1:)/', $commentBody)) {
            return -1;
        }
        return 0;
    }
}
