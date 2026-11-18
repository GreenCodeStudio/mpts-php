<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;

class TEOrEmpty extends TEExpression
{
    public TEExpression $left;
    public TEExpression $right;

    public function __construct(TEExpression $left, TEExpression $right)
    {
        $this->left = $left;
        $this->right = $right;
    }

    public function execute(Environment $env)
    {
        $subEnv = clone $env;
        $subEnv->allowUndefined = true;
        $left = $this->left->execute($subEnv);
        if ($left !== null&&$left !== ''&&$left !== 0 && $left!==false)
            return $left;
        else
            return $this->right->execute($env);
    }
}
