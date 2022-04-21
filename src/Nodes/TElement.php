<?php

namespace MKrawczyk\Mpts\Nodes;
use MKrawczyk\Mpts\Environment;

class TElement
{
    public string $tagName = "";
    public array $children = [];
    public array $attributes = [];
    public function execute (Environment $env){
        $ret=$env->document->createElement($this->tagName);
        foreach ($this->attributes as $attr){
            $ret->setAttribute($attr->name, $attr->expression->execute($env));
        }
        foreach ($this->children as $child){
            $ret->appendChild($child->execute($env));
        }
        return $ret;
    }
}