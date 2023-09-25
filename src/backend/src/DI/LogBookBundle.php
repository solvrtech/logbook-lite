<?php

namespace App\DI;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class LogBookBundle extends Bundle
{
    /**
     * {@inheritDoc}
     *
     * @return LogBookExtension
     */
    public function getContainerExtension(): LogBookExtension
    {
        return new LogBookExtension();
    }
}