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
        return (new XMLParser($text))->parseNormal();
    }

    protected function addElement(TNode $element, bool $selfclose = false)
    {
        $parent = $this->openElements[count($this->openElements) - 1];

        $parent->addChild($element);
        if (!$selfclose)
            $this->openElements[] = $element;
    }
}
