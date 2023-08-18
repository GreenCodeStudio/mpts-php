<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\Mpts\Environment;

class TExpressionSubnode extends TNode
{
    public function execute(Environment $env):\DOMDocumentFragment
    {
        $frag=$env->document->createDocumentFragment();
        $frag->appendXML($this->expression->execute($env));
        return $frag;
    }
}