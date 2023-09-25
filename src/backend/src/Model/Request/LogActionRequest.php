<?php

namespace App\Model\Request;

use App\Common\Config\LogConfig;
use Symfony\Component\Validator\Constraints as Assert;

class LogActionRequest
{
    #[Assert\NotBlank(groups: ['status'])]
    #[Assert\Choice(
        [
            LogConfig::NEW,
            LogConfig::ON_REVIEW,
            LogConfig::IGNORED,
            LogConfig::RESOLVED
        ],
        groups: ['status']
    )]
    private ?string $status = null;

    private ?string $priority = null;

    private ?int $assignee = null;

    #[Assert\Type('array', groups: ['tag'])]
    private ?array $tag = null;

    #[Assert\IsTrue(message: "Priority not valid", groups: ['priority'])]
    public function isPriorityValid(): bool
    {
        if (!empty($this->priority)) {
            return in_array(
                $this->priority,
                array(
                    LogConfig::HIGHEST_PRIORITY,
                    LogConfig::CRITICAL_PRIORITY,
                    LogConfig::HIGH_PRIORITY,
                    LogConfig::MEDIUM_PRIORITY,
                    LogConfig::LOW_PRIORITY
                )
            );
        }

        return true;
    }

    #[Assert\IsTrue(message: "Assignee not valid", groups: ['assignee'])]
    public function isAssigneeValid(): bool
    {
        if (null !== $this->assignee) {
            return is_int($this->assignee);
        }

        return true;
    }

    #[Assert\IsTrue(message: "Tags not valid", groups: ['tag'])]
    public function isTagValid(): bool
    {
        if (null !== $this->tag) {
            return !self::hasDuplicate($this->tag) ||
                254 > strlen(json_encode($this->tag));
        }

        return true;
    }

    /**
     * Check array has duplicate values.
     *
     * @param array $array
     *
     * @return bool
     */
    private function hasDuplicate(array $array): bool
    {
        return count($array) !== count(array_flip($array));
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(?string $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getAssignee(): ?int
    {
        return $this->assignee;
    }

    public function setAssignee(?int $assignee): self
    {
        $this->assignee = $assignee;

        return $this;
    }

    public function getTag(): ?string
    {
        $tag = $this->tag ?: [];

        return strtolower(json_encode($tag));
    }

    public function setTag(?array $tag): self
    {
        $this->tag = $tag;

        return $this;
    }
}