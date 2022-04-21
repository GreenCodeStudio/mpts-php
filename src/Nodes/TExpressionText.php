<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\Mpts\Environment;

class TExpressionText
{
    public function execute(Environment $env)
    {
        return $env->document->createTextNode($this->expression->execute($env));
    }
}