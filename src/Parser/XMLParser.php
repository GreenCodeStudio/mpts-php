<?php

namespace MKrawczyk\Mpts\Parser;

use MKrawczyk\Mpts\Nodes\Expressions\TEString;
use MKrawczyk\Mpts\Nodes\TAttribute;
use MKrawczyk\Mpts\Nodes\TComment;
use MKrawczyk\Mpts\Nodes\TDocumentFragment;
use MKrawczyk\Mpts\Nodes\TElement;
use MKrawczyk\Mpts\Nodes\TExpressionText;
use MKrawczyk\Mpts\Nodes\TForeach;
use MKrawczyk\Mpts\Nodes\TIf;
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
            $elementChildren=$element->children;
            $last = end($elementChildren);
            if ($char == '<') {
                if (substr($this->text, $this->position, 4) == '<!--') {
                    $this->position += 4;
                    $text = $this->readUntillText('-->');
                    $this->position += 3;
                    $element->children[] = new TComment($text);
                } else if ($this->text[$this->position + 1] == '/') {
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
                        $element->addChild($result->element);
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
                    $element->addChild( $last);
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

    protected function convertToSpecialElement($result, $element)
    {
        if (strtolower($result->element->tagName) == ":if") {
            $node = new TIf();
            $expression = $result->element->getAttribute('condition')->expression;
            $node->conditions[] = (object)['expression' => $expression, 'children' => []];
            $element->children[] = $node;

            if (!$result->autoclose)
                $this->openElements[] = $node;
        } else if (strtolower($result->element->tagName) == ":else-if") {
            $last = end($element->children);
            if (!($last instanceof TIf && $last->else == null))
                throw new MptsParserError("need if before else-if");

            $expression = $result->element->getAttribute('condition')->expression;
            $last->conditions[] = (object)['expression' => $expression, 'children' => []];

            if (!$result->autoclose)
                $this->openElements[] = $last;
        } else if (strtolower($result->element->tagName) == ":else") {
            $last = end($element->children);
            if (!($last instanceof TIf && $last->else == null))
                throw new MptsParserError("need if before else");

            $last->else = (object)['children' => []];

            if (!$result->autoclose)
                $this->openElements[] = $last;
        } else if (strtolower($result->element->tagName) == ":loop") {
            $count = $result->element->getAttribute('count')->expression;
            $node = new TLoop($count);
            $element->children[] = $node;
            if (!$result->autoclose)
                $this->openElements[] = $node;
        } else if (strtolower($result->element->tagName) == ":foreach") {
            $collection = $result->element->getAttribute('collection')->expression;
            $item = $result->element->getAttribute('item')?->expression->name;
            $key = $result->element->getAttribute('key')?->expression->name;
            $node = new TForeach($collection, $item, $key);

            $element->children[] = $node;

            if (!$result->autoclose)
                $this->openElements[] = $node;
        }
    }

    protected function closeSpecialElement(string $tagName)
    {
        $tagName = strtolower($tagName);
        $last = end($this->openElements);
        if ($tagName == ':if') {
            if ($last instanceof TIf && count($last->conditions) == 1 && $last->else == null) {
                array_pop($this->openElements);
            } else {
                throw new MptsParserError("Last opened element is not <:if>");
            }
        } elseif ($tagName == ':else-if') {
            if ($last instanceof TIf && count($last->conditions) > 1 && $last->else == null) {
                array_pop($this->openElements);
            } else {
                throw new MptsParserError("Last opened element is not <:else-if>");
            }
        } elseif ($tagName == ':else') {
            if ($last instanceof TIf && $last->else != null) {
                array_pop($this->openElements);
            } else {
                throw new MptsParserError("Last opened element is not <:else>");
            }
        } elseif ($tagName == ':loop') {
            if ($last instanceof TLoop) {
                array_pop($this->openElements);
            } else {
                throw new MptsParserError("Last opened element is not <:loop>");
            }
        } elseif ($tagName == ':foreach') {
            if ($last instanceof TForeach) {
                array_pop($this->openElements);
            } else {
                throw new MptsParserError("Last opened element is not <:foreach>");
            }
        }
    }
}