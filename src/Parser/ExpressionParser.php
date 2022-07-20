<?php

namespace MKrawczyk\Mpts\Parser;

use MKrawczyk\Mpts\Nodes\Expressions\TEBoolean;
use MKrawczyk\Mpts\Nodes\Expressions\TEEqual;
use MKrawczyk\Mpts\Nodes\Expressions\TENumber;
use MKrawczyk\Mpts\Nodes\Expressions\TEString;
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

    public function parseNormal($endLevel=0)
    {
        $lastNode = null;
        while ($this->position < strlen($this->text)) {
            $char = $this->text[$this->position];
            if (preg_match("/\s/", $char)) {
                $this->position++;
            } else if (preg_match("/[0-9\.\-+]/", $char)) {
                $value = $this->readUntill("/\s/");
                $lastNode = new TENumber(+$value);
            } else if ($char == '"') {
                $this->position++;
                $lastNode = new TEString($this->readUntill('/"/'));
                $this->position++;
            } else if ($char == "'") {
                $this->position++;
                $lastNode = new TEString($this->readUntill("/'/"));
                $this->position++;
            }else if ($char == "(") {
                $this->position++;
                $value = $this->parseNormal(1);
                $this->position++;
                $lastNode = $value;
            } else if ($char == ")") {
                if ($endLevel >= 1) {
                    break;
                } else {
                    throw new \Exception("( not opened");
                }
            } else if ($char == '=' && $this->text[$this->position + 1] == "=") {
                $this->position += 2;
                $right = $this->parseNormal();
                $lastNode = new TEEqual($lastNode, $right);
            } else {
                $name = $this->readUntill("/['\"\(\)=\s]/");
                if ($name == "true") {
                    $lastNode = new TEBoolean(true);
                } else if ($name == "false") {
                    $lastNode = new TEBoolean(false);
                } else if($name=='') {
                    Throw new \Exception("Empty variable name");
                }else {
                    $lastNode = new TEVariable($name);
                }
            }
        }
        return $lastNode;
    }
}