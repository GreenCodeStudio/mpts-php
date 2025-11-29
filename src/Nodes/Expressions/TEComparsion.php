<?php

namespace MKrawczyk\Mpts\Nodes\Expressions;

use MKrawczyk\Mpts\Environment;

class TEComparsion extends TEExpression
{
    public TEExpression $left;
    public TEExpression $right;
    public bool $isGreaterThan;
    public bool $orEqual;

    public function __construct(TEExpression $left, TEExpression $right, bool $isGreaterThan, bool $orEqual)
    {
        $this->left = $left;
        $this->right = $right;
        $this->isGreaterThan = $isGreaterThan;
        $this->orEqual = $orEqual;
    }

    public function execute(Environment $env): string
    {
        $l=$this->left->execute($env) ;$r= $this->right->execute($env);
        if($this->isGreaterThat){
            if($this->orEqual){
                return ($l>=$r) ;
            }else{
                return ($l>$r) ;
            }
        }else{
            if($this->orEqual){
                return ($l<=$r) ;
            }else{
                return ($l<$r) ;
            }
        }
    }
}
