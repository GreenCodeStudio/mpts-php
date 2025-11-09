<?php

namespace MKrawczyk\Mpts\Parser;

class MptsParserError extends \Exception
{
    public string $messageRaw;
    public int $lineRaw;
    public int $column;
    public string $sample;

    public function __construct(string $message, int $line, int $column, string $sample, ?string $fileName = null)
    {
        parent::__construct($message."\r\n".preg_replace("/\n/", '\\n', $sample)."\r\n".($fileName??'unknownFile').":".$line.":".$column);

        $this->messageRaw = $message;
        $this->lineRaw = $line;
        $this->column = $column;
        $this->sample = $sample;
    }
}
