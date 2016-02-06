<?php

namespace PlusPull\GitHub;

class Label
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}
