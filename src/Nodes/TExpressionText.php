<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\Mpts\Environment;
use MKrawczyk\Mpts\Nodes\Expressions\TEExpression;

class TExpressionText extends TNode
{
    public ?TEExpression $expression;

    public function execute(Environment $env): \DOMText
    {
        $value = $this->expression->execute($env);
        if (is_string($value)) {
            $string = $value;
        } else if (is_object($value) && method_exists($value, '__toString')) {
            $string = $value->__toString();
        } else {
            $string = print_r($value, 1);
        }
        return $env->document->createTextNode($string);
    }
}
