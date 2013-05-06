<?php

namespace PlusPush;

class PullRequestChecker
{
    public function checkComments($comments, $required = 3)
    {
        $total = 0;
        foreach ($comments as $comment) {
            $commentBody = $comment['body'];

            if ($this->isBlocker($commentBody)) {
                return false;
            }
            $total += $this->getCommentValue($commentBody);
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
