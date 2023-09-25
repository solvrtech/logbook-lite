<?php

namespace App\Entity;

interface ResponseEntityInterface
{
    public function toResponse(): mixed;
}