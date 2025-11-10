<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;

class TENegate extends TEExpression
{
    public TEExpression $value;

    public function __construct(TEExpression $value)
    {
        $this->value = $value;
    }

    public function execute(Environment $env)
    {
        return ! $this->value->execute($env);
    }
}
