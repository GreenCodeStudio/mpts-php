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
    public function scope(array $newVariables=[]):Environment{
        $ret=clone $this;
        $ret->variables=array_merge($ret->variables, $newVariables);
        return $ret;
    }
}