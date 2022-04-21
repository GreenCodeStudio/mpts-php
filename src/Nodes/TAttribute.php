<?php

namespace MKrawczyk\Mpts\Nodes;
class TAttribute
{
    public function __construct($name, $expression)
    {
        $this->name = $name;
        $this->expression = $expression;
    }
}