<?php

namespace PlusPull\GitHub;

class Comment
{
    public $login;

    public $body;

    public function __construct($login, $body)
    {
        $this->login = $login;
        $this->body = $body;
    }
}
