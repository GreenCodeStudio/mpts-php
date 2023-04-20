<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;

class TESubtract extends TEExpression
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
        return $this->left->execute($env) - $this->right->execute($env);
    }
}