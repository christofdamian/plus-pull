<?php

namespace PlusPush\GitHub;

class PullRequest
{
    public $number;

    public $title;

    public $comments = array();

    public $statuses = array();

    public function checkComments($required = 3)
    {
        $total = 0;
        foreach ($this->comments as $comment) {
            if ($this->isBlocker($comment)) {
                return false;
            }
            $total += $this->getCommentValue($comment);
        }
        return $total >= $required;
    }

    public function isBlocker($comment)
    {
        return preg_match('/^\[B\]/', $comment);
    }

    public function getCommentValue($comment)
    {
        if (preg_match('/^(\+1\b|:\+1:)/', $comment)) {
            return 1;
        }
        if (preg_match('/^(\-1\b|:\-1:)/', $comment)) {
            return -1;
        }
        return 0;
    }
}
