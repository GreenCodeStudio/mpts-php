<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\Mpts\Environment;

class TDocumentFragment extends TNode
{
    public array $children = [];

    public function execute(Environment $env): \DOMDocumentFragment
    {
        $ret = $env->document->createDocumentFragment();
        foreach ($this->children as $child) {
            $result=$child->execute($env);
            if($result instanceof \DOMDocumentFragment && !$result->firstChild) continue;
            if($result) {
                $ret->appendChild($result);
            }
        }
        return $ret;
    }
}
