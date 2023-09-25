<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class HealthStatusRequest
{
    #[Assert\NotBlank]
    private ?string $datetime = null;

    #[Assert\NotBlank]
    private ?string $instanceId = null;

    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Collection([
            'key' => [
                new Assert\NotBlank(),
            ],
            'status' => [
                new Assert\NotBlank(),
            ],
            'meta' => [
                new Assert\Type('array'),
            ],
        ]),
    ])]
    private ?array $checks = null;

    public function getDatetime(): ?string
    {
        return $this->datetime;
    }

    public function setDatetime(?string $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getInstanceId(): ?string
    {
        return $this->instanceId;
    }

    public function setInstanceId(?string $instanceId): self
    {
        $this->instanceId = $instanceId;

        return $this;
    }

    public function getChecks(): ?array
    {
        return $this->checks;
    }

    public function setChecks(?array $checks): self
    {
        $this->checks = $checks;

        return $this;
    }
}