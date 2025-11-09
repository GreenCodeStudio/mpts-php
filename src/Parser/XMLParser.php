<?php

namespace MKrawczyk\Mpts\Parser;

use MKrawczyk\Mpts\Nodes\TElement;
use MKrawczyk\Mpts\Nodes\TNode;

class XMLParser extends AbstractMLParser
{
    protected array $voidElements = [];
    protected array $onlySiblingsElements = [];
    protected bool $allowAutoClose = false;

    public static function Parse(string $text)
    {
        return (new self($text))->parseNormal();
    }

    public static function ParseFile(string $filePath)
    {
        $fullPath = realpath($filePath);
        return (new self(file_get_contents($filePath), $fullPath))->parseNormal();
    }

    protected function addElement(TNode $element, bool $selfclose = false)
    {
        $parent = $this->openElements[count($this->openElements) - 1];

        $parent->addChild($element);
        if (!$selfclose)
            $this->openElements[] = $element;
    }
}
