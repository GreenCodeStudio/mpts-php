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
        if ($env->allowUndefined) {
            if (is_array($obj))
                return $obj[$name] ?? null;
            else
                return ($obj->$name) ?? null;
        } else {
            if (is_array($obj))
                return $obj[$name];
            else if (isset($obj->$name))
                return $obj->$name;
            else if (is_object($obj) && method_exists($obj, $name))
                return fn(...$args) => $obj->$name(...$args);
        }
    }
}
