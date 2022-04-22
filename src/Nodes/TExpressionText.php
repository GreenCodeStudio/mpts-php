<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\Mpts\Environment;

class TExpressionText extends TNode
{
    public function execute(Environment $env):\DOMText
    {
        return $env->document->createTextNode($this->expression->execute($env));
    }
}