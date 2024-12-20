<?php

namespace MKrawczyk\Mpts\Parser;

use MKrawczyk\Mpts\Nodes\Expressions\TEAdd;
use MKrawczyk\Mpts\Nodes\Expressions\TEBoolean;
use MKrawczyk\Mpts\Nodes\Expressions\TEConcatenate;
use MKrawczyk\Mpts\Nodes\Expressions\TEEqual;
use MKrawczyk\Mpts\Nodes\Expressions\TEMethodCall;
use MKrawczyk\Mpts\Nodes\Expressions\TENumber;
use MKrawczyk\Mpts\Nodes\Expressions\TEOrNull;
use MKrawczyk\Mpts\Nodes\Expressions\TEProperty;
use MKrawczyk\Mpts\Nodes\Expressions\TEString;
use MKrawczyk\Mpts\Nodes\Expressions\TESubtract;
use MKrawczyk\Mpts\Nodes\Expressions\TEVariable;

class ExpressionParser extends AbstractParser
{
    public string $text;
    public int $position = 0;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public static function Parse(string $text)
    {
        return (new ExpressionParser($text))->parseNormal();
    }

    public function parseNormal($endLevel = 0)
    {
        $lastNode = null;
        while ($this->position < strlen($this->text)) {
            $char = $this->text[$this->position];
            if (preg_match("/\s/", $char)) {
                $this->position++;
            } else if ($lastNode && $char == '.') {
                $this->position++;
                $name = $this->readUntill('/[\'"\(\)=\.:\s>+\-*?]/');
                $lastNode = new TEProperty($lastNode, $name);
            } else if (!$lastNode && preg_match("/[0-9\.]/", $char)) {
                $this->position++;
                $value = $char.$this->readUntill("/[^0-9\.e]/");
                $lastNode = new TENumber((float)$value);
            } else if ($char == '"') {
                $this->position++;
                $lastNode = new TEString($this->decodeEntities($this->readUntill('/"/')));
                $this->position++;
            } else if ($char == "'") {
                $this->position++;
                $lastNode = new TEString($this->decodeEntities($this->readUntill("/'/")));
                $this->position++;
            } else if ($char == "(") {
                if ($lastNode) {
                    $lastNode = new TEMethodCall($lastNode);
                    $this->position++;
                    $this->skipWhitespace();
                    while ($this->position<strlen($this->text) && $this->text[$this->position] != ')') {
                        if ($this->position >= strlen($this->text)) throw new \Exception("Unexpected end of input");

                        $value = $this->parseNormal(2);
                        $lastNode->args[] = $value;
                        if($this->position<strlen($this->text) && $this->text[$this->position] ==',')
                            $this->position++;
                    }
                    $this->position++;
                } else {
                    $this->position++;
                    $value = $this->parseNormal(1);
                    $this->position++;
                    $lastNode = $value;
                }
            } else if ($char == ")") {
                if ($endLevel >= 1) {
                    break;
                } else {
                    throw new \Exception("( not opened");
                }
            } else if ($char == '=' && $this->text[$this->position + 1] == "=") {
                $this->position += 2;
                $right = $this->parseNormal(2);
                $lastNode = new TEEqual($lastNode, $right);
            } else if ($char == '?' && $this->text[$this->position + 1] == "?") {
                if ($endLevel >= 5) {
                    break;
                }
                $this->position += 2;
                $right = $this->parseNormal(5);
                $lastNode = new TEOrNull($lastNode, $right);
            } else if ($char == '+') {
                if ($endLevel >= 4) {
                    break;
                }
                $this->position += 1;
                $right = $this->parseNormal(4);
                $lastNode = new TEAdd($lastNode, $right);
            } else if ($char == '-') {
                if ($endLevel >= 4) {
                    break;
                }
                $this->position += 1;
                $right = $this->parseNormal(4);
                $lastNode = new TESubtract($lastNode, $right);
            } else if ($char == ':') {
                $this->position += 1;
                $right = $this->parseNormal(3);
                $lastNode = new TEConcatenate($lastNode, $right);
            } else if ($char == ">" || $char == "\\") {
                if ($lastNode) {
                    break;
                } else {
                    throw new \Exception("Unexpected character");
                }
            } else {
                if ($lastNode) {
                    break;
                }
                $name = $this->readUntill("/['\"\(\)=\.\s:>\/+\-*?,]/");
                if ($name == "true") {
                    $lastNode = new TEBoolean(true);
                } else if ($name == "false") {
                    $lastNode = new TEBoolean(false);
                } else if ($name == '') {
                    throw new \Exception("Empty variable name");
                } else {
                    $lastNode = new TEVariable($name);
                }
            }
        }
        return $lastNode;
    }

    private function decodeEntities(string $raw)
    {
        return html_entity_decode($raw, ENT_QUOTES|ENT_HTML5);
    }
}
