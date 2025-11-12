<?php

namespace MKrawczyk\Mpts\Parser;

use MKrawczyk\Mpts\CodePosition;

abstract class  AbstractParser
{
    protected string $text;
    protected ?string $fileName = null;
    protected ?int $fileLineOffset = null;
    protected ?int $fileColumnOffset = null;
    protected ?int $filePositionOffset = null;
    protected int $position;
    protected array $openElements;

    protected function readUntill($regexp)
    {
        $ret = "";

        while ($this->position < strlen($this->text)) {
            $char = $this->text[$this->position];
            if (preg_match($regexp, $char)) break;
            $ret .= $char;
            $this->position++;
        }
        return $ret;
    }

    protected function skipWhitespace()
    {
        return $this->readUntill("/\S/");
    }

    protected function readUntillText(string $text)
    {
        $ret = "";

        while ($this->position < strlen($this->text)) {
            $char = $this->text[$this->position];
            if (substr($this->text, $this->position, strlen($text)) == $text) break;
            $ret .= $char;
            $this->position++;
        }
        return $ret;
    }

    protected function throw($message)
    {
        throw new MptsParserError($message, $this->currentCodePosition(), substr($this->text, $this->position, 10));
    }

    public function currentLineOffset()
    {
        return substr_count(substr($this->text, 0, $this->position), "\n") + ($this->fileLineOffset ?? 1);
    }

    public function currentColumnOffset()
    {
        $lines = explode("\n", substr($this->text, 0, $this->position));
        $lineNumber = count($lines) - 1;
        $startLine = strlen($lines[$lineNumber]);
        return $startLine + ($lineNumber == 0 ? ($this->fileColumnOffset ?? 0) : 0);
    }

    public function currentFilePosition()
    {
        return $this->position + ($this->filePositionOffset ?? 0);
    }

    public function currentCodePosition(): CodePosition
    {
        return new CodePosition(
            $this->fileName,
            $this->currentLineOffset(),
            $this->currentColumnOffset(),
            $this->currentFilePosition()
        );
    }
}
