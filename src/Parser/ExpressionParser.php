<?php

namespace MKrawczyk\Mpts\Parser;

use MKrawczyk\Mpts\CodePosition;
use MKrawczyk\Mpts\Nodes\Expressions\TEAdd;
use MKrawczyk\Mpts\Nodes\Expressions\TEAnd;
use MKrawczyk\Mpts\Nodes\Expressions\TEBoolean;
use MKrawczyk\Mpts\Nodes\Expressions\TEComparsion;
use MKrawczyk\Mpts\Nodes\Expressions\TEConcatenate;
use MKrawczyk\Mpts\Nodes\Expressions\TEDivide;
use MKrawczyk\Mpts\Nodes\Expressions\TEEqual;
use MKrawczyk\Mpts\Nodes\Expressions\TEMethodCall;
use MKrawczyk\Mpts\Nodes\Expressions\TEModulo;
use MKrawczyk\Mpts\Nodes\Expressions\TEMultiply;
use MKrawczyk\Mpts\Nodes\Expressions\TENegate;
use MKrawczyk\Mpts\Nodes\Expressions\TENotEqual;
use MKrawczyk\Mpts\Nodes\Expressions\TENumber;
use MKrawczyk\Mpts\Nodes\Expressions\TEOr;
use MKrawczyk\Mpts\Nodes\Expressions\TEOrNull;
use MKrawczyk\Mpts\Nodes\Expressions\TEProperty;
use MKrawczyk\Mpts\Nodes\Expressions\TEString;
use MKrawczyk\Mpts\Nodes\Expressions\TESubtract;
use MKrawczyk\Mpts\Nodes\Expressions\TEVariable;

class ExpressionParser extends AbstractParser
{
    public string $text;
    public int $position = 0;

    public function __construct(string $text, ?string $fileName = null, ?int $filePositionOffset = null, ?int $fileLineOffset = null, ?int $fileColumnOffset = null)
    {
        $this->text = $text;
        $this->filePositionOffset = $filePositionOffset;
        $this->fileLineOffset = $fileLineOffset;
        $this->fileColumnOffset = $fileColumnOffset;
        $this->fileName = $fileName;
    }

    public static function Parse(string $text, ?CodePosition $codePosition = null)
    {
        return (new ExpressionParser($text, $codePosition?->fileName, $codePosition?->fileOffset, $codePosition?->lineNumber, $codePosition?->columnNumber))->parseNormal();
    }

    public function parseNormal($endLevel = 0)
    {
        $lastNode = null;
        while ($this->position < strlen($this->text)) {
            $char = $this->text[$this->position];
            $partCodePosition = $this->currentCodePosition();
            if (preg_match("/\s/", $char)) {
                if ($endLevel == 1)
                    break;
                else
                    $this->position++;
            } else if ($lastNode && $char == '?' && ($this->text[$this->position + 1] ?? '') == '.') {
                if (!$lastNode) {
                    $this->throw("Unexpected '?.'");
                }
                $this->position += 2;
                $partCodePosition = $this->currentCodePosition();
                $name = $this->readUntill('/[\'"\(\)=\.:\s>+\-*?]/');
                $lastNode = new TEProperty($lastNode, $name, true);
                $lastNode->codePosition = $partCodePosition;
            } else if ($lastNode && $char == '.') {
                if (!$lastNode) {
                    $this->throw("Unexpected '.'");
                }
                $this->position++;
                $partCodePosition = $this->currentCodePosition();
                $name = $this->readUntill('/[\'"\(\)=\.:\s>+\-*?]/');
                $lastNode = new TEProperty($lastNode, $name);
                $lastNode->codePosition = $partCodePosition;
            } else if (!$lastNode && preg_match("/[0-9\.]/", $char)) {
                $this->position++;
                $value = $char.$this->readUntill("/[^0-9\.e]/");
                if (preg_match("/^(\.e*|e+)/", $char)) {
                    $this->position--;
                    $this->throw("Unexpected '$char'");
                }
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
                    $lastNode->codePosition = $partCodePosition;
                    $this->position++;
                    $this->skipWhitespace();
                    while ($this->position < strlen($this->text) && $this->text[$this->position] != ')') {
                        if ($this->position >= strlen($this->text)) $this->throw("Unexpected end of input");

                        $value = $this->parseNormal(11);
                        $lastNode->args[] = $value;
                        if ($this->position < strlen($this->text) && $this->text[$this->position] == ',')
                            $this->position++;
                    }
                    $this->position++;
                } else {
                    $this->position++;
                    $value = $this->parseNormal(10);
                    $lastNode = $value;
                }
            } else if ($char == ")") {
                if ($endLevel == 10) {
                    $this->position++;
                    break;
                } else if ($endLevel >= 10) {
                    break;
                } else {
                    $this->throw("( not opened");
                }
            } else if ($char == '=') {
                if ($this->text[$this->position + 1] != "=") {
                    $this->throw("Assignment '=' is not allowed in expressions");
                }
                if ($endLevel >= 40) {
                    break;
                }
                $this->position += 2;
                $right = $this->parseNormal(40);
                $lastNode = new TEEqual($lastNode, $right);
            } else if ($char == '!' && $this->text[$this->position + 1] == "=") {
                if ($endLevel >= 40) {
                    break;
                }
                $this->position += 2;
                $right = $this->parseNormal(40);
                $lastNode = new TENotEqual($lastNode, $right);
            } else if ($char == '&' && $this->text[$this->position + 1] == "&") {
                if ($endLevel >= 20) {
                    break;
                }
                $this->position += 2;
                $right = $this->parseNormal(20);
                $lastNode = new TEAnd($lastNode, $right);
            } else if ($char == '|' && $this->text[$this->position + 1] == "|") {
                $this->position += 2;
                $right = $this->parseNormal(20);
                $lastNode = new TEOr($lastNode, $right);
            } else if ($char == '?' && $this->text[$this->position + 1] == "?") {
                if ($endLevel >= 20) {
                    break;
                }
                $this->position += 2;
                $right = $this->parseNormal(20);
                $lastNode = new TEOrNull($lastNode, $right);
            } else if ($char == '+') {
                if ($endLevel >= 60) {
                    break;
                }
                $this->position += 1;
                $right = $this->parseNormal(60);
                $lastNode = new TEAdd($lastNode, $right);
            } else if ($char == '-') {
                if ($endLevel >= 60) {
                    break;
                }
                $this->position += 1;
                $right = $this->parseNormal(60);
                $lastNode = new TESubtract($lastNode, $right);
            } else if ($char == '*') {
                if ($endLevel >= 70) {
                    break;
                }
                $this->position += 1;
                $right = $this->parseNormal(70);
                $lastNode = new TEMultiply($lastNode, $right);
            } else if ($char == '/' && $this->text[$this->position + 1] != '>') {
                if ($endLevel >= 70) {
                    break;
                }
                $this->position += 1;
                $right = $this->parseNormal(70);
                $lastNode = new TEDivide($lastNode, $right);
            } else if ($char == '%') {
                if ($endLevel >= 70) {
                    break;
                }
                $this->position += 1;
                $right = $this->parseNormal(70);
                $lastNode = new TEModulo($lastNode, $right);
            } else if ($char == '!') {
                if ($endLevel > 30) {
                    break;
                }
                if ($lastNode) {
                    $this->throw("unexpected '!'");
                }
                $this->position += 1;
                $right = $this->parseNormal(30);
                $lastNode = new TENegate($right);
            } else if ($char == ':') {
                if ($endLevel >= 50) {
                    break;
                }
                $this->position += 1;
                $right = $this->parseNormal(50);
                $lastNode = new TEConcatenate($lastNode, $right);
            } else if ($char == ">") {
                if ($endLevel == 0 || $endLevel == 1) {
                    if ($lastNode) {
                        break;
                    } else {
                        $this->throw("Unexpected character");
                    }
                } else {//in parenthesis
                    if ($endLevel >= 40) {
                        break;
                    }
                    $this->position++;
                    $orEqual = $this->text[$this->position] == '=';
                    if ($orEqual) {
                        $this->position++;
                    }
                    $right = $this->parseNormal(40);
                    $lastNode = new TEComparsion($lastNode, $right, true, $orEqual);

                }
            } else if ($char == "<") {
                if ($endLevel >= 40) {
                    break;
                }
                $this->position++;
                $orEqual = $this->text[$this->position] == '=';
                if ($orEqual) {
                    $this->position++;
                }
                $right = $this->parseNormal(40);
                $lastNode = new TEComparsion($lastNode, $right, true, $orEqual);

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
                    $this->throw("Empty variable name");
                } else {
                    $lastNode = new TEVariable($name, $partCodePosition);
                }
            }
        }
        return $lastNode;
    }

    private function decodeEntities(string $raw)
    {
        return html_entity_decode($raw, ENT_QUOTES | ENT_HTML5);
    }
}
