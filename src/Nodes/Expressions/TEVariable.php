<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\CodePosition;
use MKrawczyk\Mpts\Environment;

class TEVariable extends TEExpression
{
    public string $name;

    public function __construct(string $name = "", ?CodePosition $codePosition = null)
    {
        $this->name = $name;
        $this->codePosition = $codePosition;
    }

    public function execute(Environment $env)
    {

        if ($env->allowUndefined) {
            return $env->variables[$this->name] ?? null;
        } else {
            if(isset($env->variables[$this->name])===false){
                $this->throw("Undefined variable: ".$this->name);
            }
            return $env->variables[$this->name];
        }
    }
}
