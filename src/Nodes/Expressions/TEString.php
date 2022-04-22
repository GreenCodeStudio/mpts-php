<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;

class TEString extends TEExpression
{
    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function execute(Environment $env): string
    {
        return $this->value;
    }
}