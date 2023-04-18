<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\FunQuery\FunQuery;
use MKrawczyk\Mpts\Environment;
use MKrawczyk\Mpts\Nodes\Expressions\TEExpression;

class TLoop extends TNode
{
    public $children = [];
    public TEExpression $count;

    public function __construct(TEExpression $count)
    {
        $this->count=$count;
    }

    public function execute(Environment $env)
    {
        $ret = $env->document->createDocumentFragment();
        $count = $this->count->execute($env);
        for($i=0;$i<$count;$i++){
            foreach ($this->children as $child) {
                $envScoped = $env->scope();
                $result = $child->execute($envScoped);
                if ($result)
                    $ret->appendChild($result);
            }
        }
        return $ret;
    }
}