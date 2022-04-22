<?php

namespace MKrawczyk\Mpts;

use DOMDocument;

class Environment
{
    public bool $allowExecution = false;
    public array $variables = [];
    public DOMDocument $document;
    public function __construct()
    {
        $this->document = new DOMDocument();
    }
}