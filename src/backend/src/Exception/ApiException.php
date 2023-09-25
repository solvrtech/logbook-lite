<?php

namespace App\Exception;

use RuntimeException;
use Throwable;

class ApiException
    extends RuntimeException
    implements ApiExceptionInterface
{
    /** @internal */
    protected $serialized;

    private array $messageData;

    public function __construct(
        $message = 'Internal Server Error',
        $messageData = [],
        $code = 500,
        Throwable $previous = null
    ) {
        unset($this->serialized);
        parent::__construct($message, $code, $previous);

        $this->messageData = $messageData;
    }

    /**
     * Message key to be used by the translation component.
     *
     * @return string
     */
    public function getMessageKey(): string
    {
        return $this->message;
    }

    /**
     * Message data to be used by the translation component.
     */
    public function getMessageData(): array
    {
        return $this->messageData;
    }

    /**
     * @internal
     */
    public function __sleep(): array
    {
        $this->serialized = $this->__serialize();

        return ['serialized'];
    }

    /**
     * Returns all the necessary state of the object for serialization purposes.
     *
     * @see __unserialize()
     */
    public function __serialize(): array
    {
        return [
            $this->token,
            $this->code,
            $this->message,
            $this->file,
            $this->line,
        ];
    }

    /**
     * @internal
     */
    public function __wakeup(): void
    {
        $this->__unserialize($this->serialized);
        unset($this->serialized);
    }

    /**
     * Restores the object state from an array given by __serialize().
     *
     * @see __serialize()
     */
    public function __unserialize(array $data): void
    {
        [
            $this->token,
            $this->code,
            $this->message,
            $this->file,
            $this->line,
        ] = $data;
    }
}