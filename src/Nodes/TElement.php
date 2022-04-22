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
            $ret->setAttribute($attr->name, $attr->expression->execute($env));
        }
        foreach ($this->children as $child) {
            $ret->appendChild($child->execute($env));
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