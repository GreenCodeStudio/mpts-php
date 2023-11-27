<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\Mpts\Environment;

abstract class TNode
{
    public abstract function execute(Environment $env);

    public function executeToString(Environment $env)
    {
        $result = $this->execute($env);
        return $env->document->saveHTML($result);
    }

    public function executeToStringXML(Environment $env)
    {
        $result = $this->execute($env);
        return $env->document->saveXML($result);
    }

    public function addChild($child)
    {
        $this->children[] = $child;
    }
}
