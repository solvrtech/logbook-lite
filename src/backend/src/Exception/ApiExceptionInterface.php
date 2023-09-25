<?php

namespace App\Exception;

interface ApiExceptionInterface
{
    /**
     * Message key to be used by the translation component.
     *
     * @return string
     */
    public function getMessageKey(): string;

    /**
     * Message data to be used by the translation component.
     */
    public function getMessageData(): array;
}