<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\CodePosition;
use MKrawczyk\Mpts\Environment;
use MKrawczyk\Mpts\MptsExecutionError;

abstract class TEExpression
{
    public ?CodePosition $codePosition = null;
    public abstract function execute(Environment $env);
    protected function throw(string $message)
    {
        throw new MptsExecutionError($message, $this->codePosition);
    }
    /*
     * Expression order
     *
     * 10: ( opening
     * 20: &&, ||, ??
     * 30:!
     * 40:==, >, >=, <, <=
     * 50: ::
     * 60;+,-
     * 70: *, /, %
     */
}
