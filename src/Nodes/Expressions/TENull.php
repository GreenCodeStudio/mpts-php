<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;

class TENull extends TEExpression
{

    public function __construct()
    {
    }

    public function execute(Environment $env)
    {
        return null;
    }
}
