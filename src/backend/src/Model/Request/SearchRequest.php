<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class SearchRequest
{
    #[Assert\Type('int')]
    #[Assert\Range(min: 0)]
    private ?int $page = null;

    #[Assert\Type('int')]
    #[Assert\Range(min: 0)]
    private ?int $size = null;

    private ?string $search = null;

    #[Assert\IsTrue(message: "Search not valid")]
    public function isSearchValid(): bool
    {
        if (null !== $this->search) {
            if (3 <= strlen($this->search)) {
                return true;
            }

            return false;
        }

        return true;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): self
    {
        if ($search) {
            $this->search = strtolower($search);
        }

        return $this;
    }

    public function offset(): int
    {
        return $this->getSize() * ($this->getPage() - 1);
    }

    public function getSize(): ?int
    {
        return $this->size ?? 25;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size > 0 ? $size : 25;

        return $this;
    }

    public function getPage(): ?int
    {
        return $this->page ?? 1;
    }

    public function setPage(?int $page): self
    {
        $this->page = $page > 0 ? $page : 1;

        return $this;
    }
}