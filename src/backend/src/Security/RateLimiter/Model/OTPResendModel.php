<?php

namespace App\Security\RateLimiter\Model;

use DateInterval;
use DateTime;

class OTPResendModel
{
    private int $availableTokens;
    private int $interval;
    private DateTime $lastConsume;

    public function __construct(
        int $availableTokens,
        int $interval
    ) {
        $this->availableTokens = $availableTokens;
        $this->interval = $interval;
        $this->lastConsume = new DateTime();
    }

    /**
     * Is accept to consume the token.
     *
     * @return bool
     */
    public function isAccepted(): bool
    {
        return
            0 < $this->availableTokens &&
            $this->lastConsume < (new DateTime())
                ->add(new DateInterval("PT{$this->interval}M"));
    }

    /**
     * Consume the available token.
     *
     * @param int $token
     *
     * @return OTPResendModel
     */
    public function consume(int $token): self
    {
        $this->availableTokens = $this->availableTokens - $token;

        return $this;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }
}