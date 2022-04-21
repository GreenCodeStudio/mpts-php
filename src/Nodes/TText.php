<?php

namespace MKrawczyk\Mpts\Nodes;

use MKrawczyk\Mpts\Environment;

class TText
{
    public string $text = "";

    public function execute(Environment $env)
    {
        return $env->document->createTextNode(html_entity_decode($this->text));
    }
}