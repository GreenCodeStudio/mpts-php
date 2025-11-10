<?php

namespace MKrawczyk\Mpts\Parser;

use MKrawczyk\Mpts\Nodes\TElement;
use MKrawczyk\Mpts\Nodes\TNode;

class HTMLParser extends AbstractMLParser
{
    protected array $voidElements = ['area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
    protected array $onlySiblingsElements = ['li', 'dt', 'dd', 'p', 'rt', 'rp', 'optgroup', 'option', 'colgroup', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th'];
    protected bool $allowAutoClose = true;

    public static function Parse(string $text, ?string $filePath = null)
    {
        return (new HTMLParser($text, $filePath))->parseNormal();
    }

    public static function ParseFile(string $filePath)
    {
        $fullPath = realpath($filePath);
        return (new self(file_get_contents($filePath), $fullPath))->parseNormal();
    }

    protected function addElement(TNode $element, bool $autoclose = false)
    {
        $parent = $this->openElements[count($this->openElements) - 1];
        if ($parent instanceof TElement && in_array(strtolower($element->tagName), $this->onlySiblingsElements) && strtolower($parent->tagName) === strtolower($element->tagName)) {
            array_pop($this->openElements);
            $parent = $this->openElements[count($this->openElements) - 1];
        }
        $parent->children[] = $element;
        if (!$autoclose && !in_array(strtolower($element->tagName), $this->voidElements))
            $this->openElements[] = $element;
    }
}
