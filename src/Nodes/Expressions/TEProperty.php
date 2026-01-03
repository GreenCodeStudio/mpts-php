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
        $parent = $this->source->execute($clonedEnv);
        if ($this->orNull || $clonedEnv->allowUndefined) {
            $env->allowUndefined = true;
        }
        $name = $this->name;
        if ($env->allowUndefined) {
            if (is_array($parent))
                return $parent[$name] ?? null;
            else
                return ($parent->$name) ?? null;
        } else {
            if (is_array($parent))
                return $parent[$name];
            else if (isset($parent->$name))
                return $parent->$name;
            else if (is_object($parent) && method_exists($parent, $name))
                return fn(...$args) => $parent->$name(...$args);
            else $this->throw('Undefined property: '.$name);
        }
    }
}
