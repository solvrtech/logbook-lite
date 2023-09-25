<?php

namespace App\Security\Authorization;

use ArrayAccess;

class UserApp implements ArrayAccess
{
    private array $array = [
        'appId' => null,
        'isTeamManager' => false,
        'logAssignee' => false,
    ];

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->array[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->array[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (isset($this->array[$offset])) {
            $this->array[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
    }

    public function getArray(): array
    {
        return $this->array;
    }

    public function setArray(array $array): self
    {
        $this->array = $array;

        return $this;
    }

    public function setAppId(int $appId): self
    {
        $this->array['appId'] = $appId;

        return $this;
    }

    public function setIsTeamManager(bool $isTeamManager): self
    {
        $this->array['isTeamManager'] = $isTeamManager;

        return $this;
    }

    public function setLogAssignee(bool $logAssignee): self
    {
        $this->array['logAssignee'] = $logAssignee;

        return $this;
    }
}