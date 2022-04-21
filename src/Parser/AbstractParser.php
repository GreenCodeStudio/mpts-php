<?php

namespace MKrawczyk\Mpts\Parser;
abstract class  AbstractParser
{

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
}