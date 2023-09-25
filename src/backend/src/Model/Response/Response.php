<?php

namespace App\Model\Response;

class Response
{
    public ?bool $success = null;
    public ?string $message = null;
    public mixed $data;

    public function __construct(
        bool   $success,
        string $message,
               $data = null
    )
    {
        $this->success = $success;
        $this->message = $message;
        if (null !== $data) {
            $this->data = $data;
        }
    }
}
