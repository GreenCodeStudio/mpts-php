<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;

abstract class TEExpression
{
    public abstract function execute(Environment $env);
}