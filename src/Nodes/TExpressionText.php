<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\Mpts\Environment;
use MKrawczyk\Mpts\Nodes\Expressions\TEExpression;

class TExpressionText extends TNode
{
    public ?TEExpression $expression;

    public function execute(Environment $env):\DOMText
    {
        return $env->document->createTextNode($this->expression->execute($env));
    }
}
