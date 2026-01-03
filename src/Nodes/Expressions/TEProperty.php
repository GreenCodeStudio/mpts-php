<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;

class TEProperty extends TEExpression
{
    public TEExpression $source;
    public string $name;
    public bool $orNull = false;

    public function __construct(TEExpression $source, string $name = "", bool $orNull = false)
    {
        $this->source = $source;
        $this->name = $name;
        $this->orNull = $orNull;
    }

    public function execute(Environment $env)
    {
        $clonedEnv = clone $env;
        $obj = $this->source->execute($clonedEnv);
        if ($this->orNull || $clonedEnv->allowUndefined) {
            $env->allowUndefined = true;
        }
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
            else $this->throw('Undefined property: '.$name);
        }
    }
}
