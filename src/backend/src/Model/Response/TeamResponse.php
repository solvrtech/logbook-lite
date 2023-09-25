<?php

namespace App\Model\Response;

use App\Entity\TeamApp;
use App\Entity\UserTeam;

class TeamResponse
{
    public ?int $id = null;
    public ?string $name = null;
    public array $userTeam;
    public ?int $totalApp = null;
    public array $apps;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUserTeam(): array
    {
        return $this->userTeam;
    }

    public function setUserTeam(array $userTeam): self
    {
        if (0 < count($userTeam)) {
            $this->userTeam = array_map(function (UserTeam $user) {
                return [
                    'userId' => $user->getUser()->getId(),
                    'role' => $user->getRole(),
                ];
            }, $userTeam);
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

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

    public function getApps(): array
    {
        return $this->apps;
    }

    public function setApps(array $apps): self
    {
        if (0 < count($apps)) {
            $this->apps = array_map(function (TeamApp $teamApp) {
                return $teamApp->getApp()->toStandardResponse();
            }, $apps);
        }

        return $this;
    }
}