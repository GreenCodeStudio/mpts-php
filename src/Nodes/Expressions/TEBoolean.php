<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;

class TEBoolean extends TEExpression
{
    public bool $value;

    public function __construct(bool $value = false)
    {
        $this->value = $value;
    }

    public function execute(Environment $env):bool
    {
        return $this->value;
    }
}