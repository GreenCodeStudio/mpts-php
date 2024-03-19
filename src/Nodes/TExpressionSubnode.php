<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\Mpts\Environment;
use MKrawczyk\Mpts\Nodes\Expressions\TEExpression;

class TExpressionSubnode extends TNode
{
    public TEExpression $expression;

    public function execute(Environment $env):\DOMDocumentFragment
    {
        $frag=$env->document->createDocumentFragment();
        $frag->appendXML($this->expression->execute($env));
        return $frag;
    }
}
