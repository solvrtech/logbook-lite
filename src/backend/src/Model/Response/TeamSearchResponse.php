<?php

namespace App\Model\Response;

class TeamSearchResponse
{
    public ?int $id = null;
    public ?string $name = null;
    public ?int $member = null;
    public ?int $totalApp = null;
    public ?bool $isTeamManager = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMember(): ?int
    {
        return $this->member;
    }

    public function setMember(?int $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getTotalApp(): ?int
    {
        return $this->totalApp;
    }

    public function setTotalApp(?int $totalApp): self
    {
        $this->totalApp = $totalApp;

        return $this;
    }

    public function getIsTeamManager(): ?bool
    {
        return $this->isTeamManager;
    }

    public function setIsTeamManager(?bool $isTeamManager): self
    {
        $this->isTeamManager = $isTeamManager;

        return $this;
    }
}