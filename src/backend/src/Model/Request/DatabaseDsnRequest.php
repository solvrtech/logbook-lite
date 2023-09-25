<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class DatabaseDsnRequest
{
    #[Assert\NotBlank]
    #[Assert\Choice(['mysql', 'postgresql'])]
    private ?string $dbms = null;

    #[Assert\NotBlank]
    private ?string $hostname = null;

    #[Assert\NotBlank]
    #[Assert\Type('int')]
    private ?int $port = null;

    #[Assert\NotBlank]
    private ?string $dbName = null;

    #[Assert\NotBlank]
    private ?string $username = null;

    #[Assert\NotBlank]
    private ?string $password = null;

    public function getDbms(): ?string
    {
        return $this->dbms;
    }

    public function setDbms(?string $dbms): self
    {
        $this->dbms = $dbms;

        return $this;
    }

    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    public function setHostname(?string $hostname): self
    {
        $this->hostname = $hostname;

        return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(?int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function getDbName(): ?string
    {
        return $this->dbName;
    }

    public function setDbName(?string $dbName): self
    {
        $this->dbName = $dbName;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }
}