<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\FunQuery\FunQuery;
use MKrawczyk\Mpts\Environment;

class TElement extends TNode
{
    public string $tagName = "";
    public array $children = [];
    public array $attributes = [];

    public function execute(Environment $env): \DOMElement
    {
        $ret = $env->document->createElement($this->tagName);
        foreach ($this->attributes as $attr) {
            if ($attr->expression == null)
                $ret->setAttribute($attr->name, $attr->name);
            else {
                $value=$attr->expression->execute($env);
                if($value!==null && $value!==false) {
                    $ret->setAttribute($attr->name, $value);
                }
            }
        }
        foreach ($this->children as $child) {
            $result = $child->execute($env);
            if (!empty($result))
                $ret->appendChild($result);
        }
        return $ret;
    }

    public function getAttribute(string $name): ?TAttribute
    {
        return FunQuery::create($this->attributes)->firstOrNull(fn($x) => $x->name == $name);
    }

    public function addChild($child)
    {
        $this->children[] = $child;
    }
}
