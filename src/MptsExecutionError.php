<?php

namespace MKrawczyk\Mpts;

class MptsExecutionError extends \Exception
{
    public string $messageRaw;
    public ?CodePosition $codePosition;

    public function __construct(string $message, ?CodePosition $codePosition)
    {
        parent::__construct($message."\r\n".$codePosition);

        $this->messageRaw = $message;
        $this->codePosition = $codePosition;
    }
}
