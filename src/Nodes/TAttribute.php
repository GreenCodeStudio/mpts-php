<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\Mpts\Nodes\Expressions\TEExpression;

class TAttribute
{
    public string $name;
    public TEExpression $expression;

    public function __construct(string $name, TEExpression $expression)
    {
        $this->name = $name;
        $this->expression = $expression;
    }
}