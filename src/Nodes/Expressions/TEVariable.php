<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;

class TEVariable
{
    private string $name;

    public function __construct(string $name = "")
    {
        $this->name = $name;
    }

    public function execute(Environment $env)
    {
        return $env->variables[$this->name];
    }
}