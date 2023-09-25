<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class NotificationDeleteRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    private array $ids;

    /**
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @param array $ids
     */
    public function setIds(array $ids): void
    {
        $this->ids = $ids;
    }
}