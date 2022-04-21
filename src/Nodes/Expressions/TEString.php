<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;

class TEString
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function execute(Environment $env)
    {
        return $this->value;
    }
}