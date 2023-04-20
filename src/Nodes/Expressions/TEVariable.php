<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;

class TEVariable extends TEExpression
{
    public string $name;

    public function __construct(string $name = "")
    {
        $this->name = $name;
    }

    public function execute(Environment $env)
    {

        if ($env->allowUndefined) {
            return $env->variables[$this->name] ?? null;
        } else {
            return $env->variables[$this->name];
        }
    }
}