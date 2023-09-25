<?php

namespace App\Entity;

use App\Common\Config\AppConfig;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`app_logo`')]
class AppLogo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: App::class)]
    #[ORM\JoinColumn(
        name: 'app_id',
        referencedColumnName: 'id',
        onDelete: 'CASCADE'
    )]
    private ?App $app = null;

    #[ORM\Column(length: 10, nullable: true, options: ['default' => AppConfig::DEFAULT])]
    private ?string $logoOption = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $initials = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $bgColor = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $publicPath = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getApp(): ?App
    {
        return $this->app;
    }

    public function setApp(?App $app): self
    {
        $this->app = $app;

        return $this;
    }

    public function getLogoOption(): ?string
    {
        return $this->logoOption ?? AppConfig::DEFAULT;
    }

    public function setLogoOption(?string $logoOption): self
    {
        $this->logoOption = $logoOption;

        return $this;
    }

    public function getInitials(): ?string
    {
        return $this->initials;
    }

    public function setInitials(?string $initials): self
    {
        $this->initials = $initials;

        return $this;
    }

    public function getBgColor(): ?string
    {
        return $this->bgColor;
    }

    public function setBgColor(?string $bgColor): self
    {
        $this->bgColor = $bgColor;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getPublicPath(): ?string
    {
        return $this->publicPath;
    }

    public function setPublicPath(?string $publicPath): self
    {
        $this->publicPath = $publicPath;

        return $this;
    }
}