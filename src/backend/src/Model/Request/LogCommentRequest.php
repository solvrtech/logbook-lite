<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class LogCommentRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 300)]
    private ?string $comment = null;

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}