<?php

namespace App\Model\Request;

use App\Common\UploadFile;
use Exception;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class AppRequest
{
    #[Assert\NotBlank(groups: ['create', 'general', 'delete'])]
    #[Assert\Length(max: 100, groups: ['create', 'general', 'delete'])]
    private ?string $name = null;

    #[Assert\NotBlank(groups: ['create', 'general'])]
    #[Assert\Length(max: 300, groups: ['create', 'general'])]
    private ?string $description = null;

    #[Assert\NotBlank(groups: ['create', 'general'])]
    #[Assert\Length(max: 50, groups: ['create', 'general'])]
    private ?string $type = null;

    #[Assert\Image(
        maxSize: '2M',
        mimeTypes: ['image/png', 'image/jpeg', 'image/jpg'],
        minWidth: 100,
        maxWidth: 100,
        maxHeight: 100,
        minHeight: 100,
        groups: ['create', 'general']
    )]
    private ?File $logo = null;

    #[Assert\Type('bool')]
    private ?bool $updateLogo = null;

    #[Assert\NotBlank(groups: ['create', 'teams'])]
    #[Assert\Type('array', groups: ['create', 'teams'])]
    #[Assert\All(
        [
            new Assert\Collection([
                'teamId' => [
                    new Assert\NotBlank(groups: ['create', 'teams']),
                    new Assert\Type('int', groups: ['create', 'teams']),
                ],
            ]),
        ]
    )]
    private ?array $team = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLogo(): ?File
    {
        return $this->logo;
    }

    /**
     * @throws Exception
     */
    public function setLogo(?string $logo): self
    {
        $this->logo = $logo ? self::decodeLogo($logo) : $logo;

        return $this;
    }

    /**
     * Decode the base64 string of logo to binary data
     *
     * @param string $logo
     *
     * @return File
     *
     * @throws Exception
     */
    private function decodeLogo(string $logo): File
    {
        $imageData = base64_decode($logo, true);

        if (false === $imageData) {
            throw new Exception("The logo contains characters from outside the base64 alphabet");
        }

        // Create a temporary file path to store the image
        $helper = new UploadFile();
        $tempFilePath = tempnam(
            $helper->createTemporaryDirectory('/app-logo'),
            'logo_'
        );

        // Rename the temporary file with the desired extension
        $extension = $helper->getExtensionOfBinary($imageData);
        $tempFilePathWithExtension = $tempFilePath.'.'.$extension;
        rename($tempFilePath, $tempFilePathWithExtension);

        file_put_contents($tempFilePathWithExtension, $imageData);

        return new File($tempFilePathWithExtension);
    }

    public function isUpdateLogo(): bool
    {
        return $this->updateLogo ?? true;
    }

    public function setUpdateLogo(?bool $updateLogo): self
    {
        $this->updateLogo = $updateLogo;

        return $this;
    }

    public function getTeam(): ?array
    {
        return $this->team;
    }

    public function setTeam(?array $team): self
    {
        $this->team = $team;

        return $this;
    }
}