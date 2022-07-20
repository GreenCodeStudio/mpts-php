<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;

class TEProperty extends TEExpression
{
    public TEExpression $source;
    public string $name;

    public function __construct(TEExpression $source, string $name = "")
    {
        $this->source = $source;
        $this->name = $name;
    }

    public function execute(Environment $env)
    {
        $obj = $this->source->execute($env);
        $name = $this->name;
        if (is_array($obj))
            return $obj[$name];
        else
            return $obj->$name;
    }
}