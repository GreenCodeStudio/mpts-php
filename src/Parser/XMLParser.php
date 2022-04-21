<?php

namespace MKrawczyk\Mpts\Parser;

use MKrawczyk\Mpts\Nodes\TDocumentFragment;
use MKrawczyk\Mpts\Nodes\TElement;
use MKrawczyk\Mpts\Nodes\TText;

class XMLParser
{
    public function __construct(string $text)
    {
        $this->text = $text;
        $this->position = 0;
        $this->openElements = [new TDocumentFragment()];
    }

    public static function Parse(string $text)
    {
        return (new XMLParser($text))->parseNormal();
    }

    private function parseNormal()
    {
        while ($this->position < strlen($this->text)) {
            $char = $this->text[$this->position];
            $element = end($this->openElements);
            $last = end($element->children);
            if ($char == '<') {
                if ($this->text[$this->position + 1] == '/') {
                    $this->position += 2;
                    $name = $this->parseElementEnd();

                    if (str_starts_with($name, ':')) {
                        $this->closeSpecialElement($name, $this->openElements);
                    } else if ($element instanceof TElement && $element->tagName == $name) {
                        array_pop($this->openElements);
                    } else {
                        throw new \Exception("Element <$name> not opened as last");
                    }

                } else {
                    $this->position++;
                    $result = $this->parseElement();
                    if (str_starts_with($result->element->tagName, ':')) {
                        $this->convertToSpecialElement($result, $element);
                    } else {
                        $element->children[] = $result->element;
                        if (!$result->autoclose)
                            $this->openElements[] = $result->element;
                    }
                }
            } else if ($char == "{" && $this->text[$this->position + 1] == "{") {
                $this->position += 2;
                $node = new TExpressionText();
                $node->expression = $result;
                $element->children[] = $node;
            } else {
                if (!$last || !($last instanceof TText)) {
                    $last = new TText();
                    $element->children[] = $last;
                }
                $last->text .= $char;
                $this->position++;
            }
        }
        return $this->openElements[0];
    }
}