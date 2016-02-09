<?php

namespace PlusPull\GitHub;

class Label
{
    public $name;
    public $color;

    public function __construct($name, $color)
    {
        $this->name = $name;
        $this->color = $color;
    }

    public function toArray()
    {
        return array(
            'name' => $this->name,
            'color' => $this->color,
        );
    }
}
