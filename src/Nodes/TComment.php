<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\Mpts\Environment;

class TComment extends TNode
{
    public string $text = "";

    public function __construct(string $text = "")
    {
        $this->text = $text;
    }

    public function execute(Environment $env): \DOMComment
    {
        return $env->document->createComment(html_entity_decode($this->text));
    }
}