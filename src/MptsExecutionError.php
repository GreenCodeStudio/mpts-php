<?php

namespace MKrawczyk\Mpts;

class MptsExecutionError extends \Exception
{
    public string $messageRaw;
    public ?CodePosition $codePosition;
    private ?\Throwable $previous;

    public function __construct(string $message, ?CodePosition $codePosition, ?\Throwable $previous = null)
    {
        parent::__construct($message."\r\n".$codePosition);

        $this->messageRaw = $message;
        $this->codePosition = $codePosition;
        $this->previous = $previous;
    }
}
