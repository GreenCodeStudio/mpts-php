<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\FunQuery\FunQuery;
use MKrawczyk\Mpts\Environment;

class TIf extends TNode
{
    public array $conditions = [];
    public $else = null;

    public function execute(Environment $env)
    {
        foreach ($this->conditions as $condition) {
            if ($condition->expression->execute($env)) {
                return $this->executeCondition($condition, $env);
            }
        }
        if ($this->else) {
            return $this->executeCondition($this->else, $env);
        }
        return null;
    }

    public function executeCondition($condition, Environment $env)
    {
        if (count($condition->children) == 1) {
            return $condition->children[0]->execute($env);
        }
        $ret = $env->document->createDocumentFragment();
        foreach ($condition->children as $child) {
            $ret->appendChild($child->execute($env));
        }
        return $ret;
    }

    public function addChild($child)
    {
        if ($this->else) return $this->else->children[] = $child;
        else end($this->conditions)->children[] = $child;
    }

    public function getChildren()
    {
        if ($this->else) return $this->else->children;
        else return end($this->conditions)->children;
    }

    public function __get($name)
    {
        if ($name == 'children')
            return $this->getChildren();
    }
}