<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\FunQuery\FunQuery;
use MKrawczyk\Mpts\Environment;
use MKrawczyk\Mpts\Nodes\Expressions\TEExpression;

class TForeach extends TNode
{
    public $children = [];
    public TEExpression $collection;
    public ?string $item;
    public ?string $key;

    public function __construct(TEExpression $collection, ?string $item = null, ?string $key = null)
    {

        $this->collection = $collection;
        $this->item = $item;
        $this->key = $key;
    }

    public function execute(Environment $env)
    {
        $ret = $env->document->createDocumentFragment();
        $collection = $this->collection->execute($env);
        $i = 0;
        foreach ($collection as $x) {
            foreach ($this->children as $child) {
                $envScoped = $env->scope();
                if ($this->item)
                    $envScoped->variables[$this->item] = $x;

                if ($this->key)
                    $envScoped->variables[$this->key] = $i;

                $ret->appendChild($child->execute($envScoped));
            }
            $i++;
        }
        return $ret;
    }
}