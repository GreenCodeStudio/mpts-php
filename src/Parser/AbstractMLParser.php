<?php

namespace MKrawczyk\Mpts\Parser;

use MKrawczyk\FunQuery\FunQuery;
use MKrawczyk\Mpts\Nodes\Expressions\TEString;
use MKrawczyk\Mpts\Nodes\TAttribute;
use MKrawczyk\Mpts\Nodes\TComment;
use MKrawczyk\Mpts\Nodes\TDocumentFragment;
use MKrawczyk\Mpts\Nodes\TElement;
use MKrawczyk\Mpts\Nodes\TExpressionSubnode;
use MKrawczyk\Mpts\Nodes\TExpressionText;
use MKrawczyk\Mpts\Nodes\TForeach;
use MKrawczyk\Mpts\Nodes\TIf;
use MKrawczyk\Mpts\Nodes\TLoop;
use MKrawczyk\Mpts\Nodes\TNode;
use MKrawczyk\Mpts\Nodes\TText;

abstract class AbstractMLParser extends AbstractParser
{
    protected bool $allowAutoClose;
    public function __construct(string $text)
    {
        $this->text = $text;
        $this->position = 0;
        $this->openElements = [new TDocumentFragment()];
    }

    abstract protected function addElement(TNode $element, bool $autoclose=false);
    protected function parseNormal()
    {
        while ($this->position < strlen($this->text)) {
            $char = $this->text[$this->position];
            $element = end($this->openElements);
            $elementChildren = $element->children;
            $last = end($elementChildren);
            if ($char == '<') {
                if (substr($this->text, $this->position, 2) == '<<') {
                    $this->position += 2;
                    $result = $this->parseExpression('>>');
                    $node = new TExpressionSubnode();
                    $node->expression = $result;
                    $element->children[] = $node;
                } else if (substr($this->text, $this->position, 4) == '<!--') {
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
                    } else if ($this->allowAutoClose && FunQuery::from($this->openElements)->any(fn($x) => $x instanceof TElement && $x->tagName == $name)) {
                        $reversed = array_reverse($this->openElements);
                        $closingElement = FunQuery::from($reversed)->first(fn($x) => $x instanceof TElement && $x->tagName == $name);
                        $indexOf = array_search($closingElement, $reversed);
                        $reversed = array_slice($reversed, $indexOf + 1);
                        $this->openElements = array_reverse($reversed);
                    } else {
                        $this->throw("Last opened element is not <$name>");
                    }

                } else {
                    $this->position++;
                    $result = $this->parseElement();
                    if (str_starts_with($result->element->tagName, ':')) {
                        $this->convertToSpecialElement($result, $element);
                    } else {
                        $this->addElement($result->element, $result->autoclose);
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
                    $element->addChild($last);
                }
                $last->text .= $char;
                $this->position++;
            }
        }
        if (count($this->openElements) > 1 && !$this->allowAutoClose) {
            $tagName = $this->openElements[count($this->openElements) - 1]->tagName;
            $this->throw("Element <$tagName> not closed");
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
                $name = $this->readUntill("/[\s=\/]/");
                $value = null;
                $this->skipWhitespace();
                $char = $this->text[$this->position];
                if ($char == '=') {
                    $this->position++;
                    $this->skipWhitespace();
                    $char2 = $this->text[$this->position];
                    if ($char2 == '(') {
                        $this->position++;
                        $value = ExpressionParser::Parse($this->readUntill('/\)/'));
                        $this->position++;
                    } else {
                        $parser = (new ExpressionParser(substr($this->text, $this->position)));
                        $value = $parser->parseNormal();
                        $this->position += $parser->position;
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
                $this->throw("need if before else-if");

            $expression = $result->element->getAttribute('condition')->expression;
            $last->conditions[] = (object)['expression' => $expression, 'children' => []];

            if (!$result->autoclose)
                $this->openElements[] = $last;
        } else if (strtolower($result->element->tagName) == ":else") {
            $last = end($element->children);
            if (!($last instanceof TIf && $last->else == null))
                $this->throw("need if before else");

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
                $this->throw("Last opened element is not <:if>");
            }
        } elseif ($tagName == ':else-if') {
            if ($last instanceof TIf && count($last->conditions) > 1 && $last->else == null) {
                array_pop($this->openElements);
            } else {
                $this->throw("Last opened element is not <:else-if>");
            }
        } elseif ($tagName == ':else') {
            if ($last instanceof TIf && $last->else != null) {
                array_pop($this->openElements);
            } else {
                $this->throw("Last opened element is not <:else>");
            }
        } elseif ($tagName == ':loop') {
            if ($last instanceof TLoop) {
                array_pop($this->openElements);
            } else {
                $this->throw("Last opened element is not <:loop>");
            }
        } elseif ($tagName == ':foreach') {
            if ($last instanceof TForeach) {
                array_pop($this->openElements);
            } else {
                $this->throw("Last opened element is not <:foreach>");
            }
        }
    }
}
