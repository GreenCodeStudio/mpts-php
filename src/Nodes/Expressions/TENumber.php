<?php


namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;

class TENumber
{
    private int|float $value;

    public function __construct(int|float $number = 0)
    {
        $this->value = $number;
    }

    public function execute(Environment $env)
    {
        return $this->value;
    }
}