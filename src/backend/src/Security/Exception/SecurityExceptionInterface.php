<?php

namespace App\Security\Exception;

interface SecurityExceptionInterface
{
    /**
     * Message data to be added to http response.
     *
     * @return array
     */
    public function getMessageData(): array;
}