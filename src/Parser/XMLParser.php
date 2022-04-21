<?php

namespace MKrawczyk\Mpts\Parser;

use MKrawczyk\Mpts\Nodes\Expressions\TEString;
use MKrawczyk\Mpts\Nodes\TAttribute;
use MKrawczyk\Mpts\Nodes\TDocumentFragment;
use MKrawczyk\Mpts\Nodes\TElement;
use MKrawczyk\Mpts\Nodes\TExpressionText;
use MKrawczyk\Mpts\Nodes\TText;

class XMLParser extends AbstractParser
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
                $result = $this->parseExpression('}}');
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

    protected function parseElement()
    {
        $autoclose = false;
        $element = new TElement();
        $element->parsePosition = $this->position;
        while ($this->position < strlen($this->text)) {
            $char = $this->text[$this->position];
            if ($char == '>' || $char == ' ' || $char == '/')
                break;
            $element->tagName .= $char;
            $this->position++;
        }
        while ($this->position < strlen($this->text)) {
            $char = $this->text[$this->position];
            if ($char == '>') {
                $this->position++;
                break;
            } else if ($char == '/') {
                $this->position++;
                $autoclose = true;
            } else if (preg_match("/\s/", $char)) {
                $this->position++;
            } else {
                $name = $this->readUntill("/[\s=]/");
                $value = null;
                $this->skipWhitespace();
                $char = $this->text[$this->position];
                if ($char == '=') {
                    $this->position++;
                    $this->skipWhitespace();
                    $char2 = $this->text[$this->position];
                    if ($char2 == '"') {
                        $this->position++;
                        $value = new TEString($this->readUntill('/"/'));
                        $this->position++;
                    } else if ($char2 == "'") {
                        $this->position++;
                        $value = new TEString($this->readUntill("/'/"));
                        $this->position++;
                    } else if ($char2 == '(') {
                        $this->position++;
                        $value = ExpressionParser::Parse($this->readUntill('/\)/'));
                        $this->position++;
                    } else {
                        $value = ExpressionParser::Parse($this->readUntill('/[\s>\/]/'));
                    }
                }
                $element->attributes[] = new TAttribute($name, $value);
            }
        }

        return (object)['element' => $element, 'autoclose' => $autoclose];
    }


    protected function parseElementEnd()
    {
        $name = "";

        while ($this->position < strlen($this->text)) {
            $char = $this->text[$this->position];
            if ($char == '>' || $char == ' ' || $char == '/')
                break;
            $name .= $char;
            $this->position++;
        }
        while ($this->position < strlen($this->text)) {
            $char = $this->text[$this->position];
            if ($char == '>') {
                $this->position++;
                break;
            }
            $this->position++;
        }
        return $name;
    }

    protected function parseExpression($end)
    {
        $text = "";

        while ($this->position < strlen($this->text)) {
            if (substr($this->text, $this->position, strlen($end)) == $end) {
                $this->position += strlen($end);
                break;
            }
            $text .= $this->text[$this->position];
            $this->position++;
        }
        return ExpressionParser::Parse($text);
    }

}