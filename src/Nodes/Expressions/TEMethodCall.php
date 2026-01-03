<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;
use MKrawczyk\FunQuery\FunQuery;
use MKrawczyk\Mpts\MptsExecutionError;

class TEMethodCall extends TEExpression
{
    public TEExpression $source;
    public array $args = [];

    public function __construct(TEExpression $source, array $args = [])
    {
        $this->source = $source;
        $this->args = $args;
    }

    public function execute(Environment $env)
    {
        $args = FunQuery::create($this->args)->map(fn($x) => $x->execute($env));

        try {
            $env = clone $env;
            $method = $this->source->execute($env);
            if ($env->allowUndefined && $method === null) {
                return null;
            }
            return $method(...$args);
        } catch (\Throwable $ex) {
            throw new MptsExecutionError($ex->getMessage(), $this->codePosition, $ex);
        }
    }
}
