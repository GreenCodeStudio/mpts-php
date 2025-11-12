<?php

namespace MKrawczyk\Mpts\Parser;

use MKrawczyk\Mpts\CodePosition;

class MptsParserError extends \Exception
{
    public string $messageRaw;
    public CodePosition $codePosition;
    public string $sample;

    public function __construct(string $message, CodePosition $codePosition, string $sample)
    {
        parent::__construct($message."\r\n".preg_replace("/\n/", '\\n', $sample)."\r\n".$codePosition);

        $this->messageRaw = $message;
        $this->codePosition = $codePosition;
        $this->sample = $sample;
    }
}
