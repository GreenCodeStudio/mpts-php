<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\Mpts\Environment;

class TText extends TNode
{
    public string $text = "";

    public function execute(Environment $env): \DOMText
    {
        return $env->document->createTextNode(html_entity_decode($this->text));
    }
}