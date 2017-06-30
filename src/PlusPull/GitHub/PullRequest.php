<?php

namespace PlusPull\GitHub;

class PullRequest
{
    public $number;

    public $title;

    public $comments = array();

    public $statuses = array();

    public $labels = array();

    public $collectedLabels = array();

    public $user;

    public $updatedAt;

    /**
     * @var boolean
     */
    public $isMergeable = false;

    public function checkComments($required = 3, $whitelist = null)
    {
        $voted = array();
        $total = 0;
        foreach ($this->comments as $comment) {
            $login = $comment->login;
            if ($whitelist && !in_array($login, $whitelist)) {
                continue;
            }

            if ($this->isBlocker($comment->body)) {
                return false;
            }

            if ($login == $this->user) {
                continue;
            }

            if (empty($voted[$login])) {
                $commentValue = $this->getCommentValue($comment->body);
                $total += $commentValue;
                $voted[$comment->login] = $commentValue!=0;
            }
        }
        return $total >= $required;
    }

    public function isBlocker($commentBody)
    {
        return preg_match('/^\s*\[B\]/', $commentBody);
    }

    public function getCommentValue($commentBody)
    {
        if (preg_match('/^\s*(\+1\b|:\+1:)/', $commentBody)) {
            return 1;
        }
        if (preg_match('/^\s*(\-1\b|:\-1:)/', $commentBody)) {
            return -1;
        }
        return 0;
    }

    public function checkStatuses()
    {
        return !empty($this->statuses) && $this->statuses['state']=='success';
    }

    public function isMergeable()
    {
        return $this->isMergeable;
    }

    public function collectCommentLabels($configuredLabels)
    {
        foreach ($this->comments as $comment) {
            foreach ($configuredLabels as $configuredLabel) {
                $hook = quotemeta($configuredLabel['hook']);
                $pattern = '/^[^~]*' . $hook . '[^~]*$/';
                if (preg_match($pattern, $comment->body)) {
                    $this->collectedLabels[] = $configuredLabel['label'];
                }
            }
        }
    }
}
