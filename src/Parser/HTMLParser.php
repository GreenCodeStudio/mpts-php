<?php

namespace MKrawczyk\Mpts\Parser;

use MKrawczyk\Mpts\Nodes\TElement;
use MKrawczyk\Mpts\Nodes\TNode;

class HTMLParser extends AbstractMLParser
{
    protected array $voidElements = ['area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
    protected array $onlySiblingsElements = ['li', 'dt', 'dd', 'p', 'rt', 'rp', 'optgroup', 'option', 'colgroup', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th'];
    protected array $closingParagraphElements = ['address', 'article', 'aside', 'blockquote', 'dir', 'div', 'dl', 'fieldset', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hgroup', 'hr', 'menu', 'nav', 'ol', 'p', 'pre', 'section', 'table', 'ul'];
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
        if ($parent instanceof TElement) {
            if (in_array(strtolower($element->tagName), $this->onlySiblingsElements) && strtolower($parent->tagName) === strtolower($element->tagName)) {
                array_pop($this->openElements);
                $parent = $this->openElements[count($this->openElements) - 1];
            } else if (strtolower($parent->tagName) == 'p' && in_array(strtolower($element->tagName), $this->closingParagraphElements)) {
                array_pop($this->openElements);
                $parent = $this->openElements[count($this->openElements) - 1];
            } else if ((strtolower($parent->tagName) == 'dd' || strtolower($parent->tagName) == 'dt') && (strtolower($element->tagName) == 'dd' || strtolower($element->tagName) == 'dt')) {
                array_pop($this->openElements);
                $parent = $this->openElements[count($this->openElements) - 1];
            }else if ((strtolower($parent->tagName) == 'rt' || strtolower($parent->tagName) == 'rp') && (strtolower($element->tagName) == 'rt' || strtolower($element->tagName) == 'rp')) {
                array_pop($this->openElements);
                $parent = $this->openElements[count($this->openElements) - 1];
            }}
            $parent->addChild($element);
            if (!$autoclose && !in_array(strtolower($element->tagName), $this->voidElements))
                $this->openElements[] = $element;
        }
    }
