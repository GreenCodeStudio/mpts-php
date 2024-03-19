<?php

namespace MKrawczyk\Mpts\Parser;
abstract class  AbstractParser
{
    protected string $text;
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
        $lines = explode("\n", substr($this->text, 0, $this->position));
        throw new MptsParserError($message, count($lines), strlen($lines[count($lines) - 1]), substr($this->text, $this->position, 10));
    }

}
