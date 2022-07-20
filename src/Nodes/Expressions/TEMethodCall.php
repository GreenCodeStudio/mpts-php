<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;
use MKrawczyk\FunQuery\FunQuery;

class TEMethodCall extends TEExpression
{
    public TEExpression $source;
    public array $args=[];

    public function __construct(TEExpression $source, array $args=[])
    {
        $this->source = $source;
        $this->args = $args;
    }

    public function execute(Environment $env)
    {
        $args=FunQuery::create($this->args)->map(fn($x)=>$x->execute($env));
        return $this->source->execute($env)(...$args);
    }
}