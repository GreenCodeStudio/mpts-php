<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\Mpts\Environment;

class TDocumentType extends TNode
{
public string $content='';
    public function execute(Environment $env): \DOMDocumentFragment
    {
        $ret = $env->document->createDocumentType($this->content);
        return $ret;
    }
}
