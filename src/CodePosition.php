<?php

namespace MKrawczyk\Mpts;
class CodePosition
{
    public function __construct(
        public ?string $fileName,
        public int $lineNumber,
        public int $columnNumber,
        public int $fileOffset
    )
    {

    }
    public function __toString(): string
    {
        return ($this->fileName ?? "<unknown>").":".$this->lineNumber.":".$this->columnNumber;
    }
}
