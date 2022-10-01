<?php

declare(strict_types=1);

namespace App\Kafka\Producer\Handler\DTO;

class Batch
{
    /**
     * @var string[]
     */
    private array $messages;

    /** @var ?callable */
    private $onSuccessCallback;

    public function __construct(array $messages, ?callable $onSuccessCallback)
    {
        $this->messages = $messages;
        $this->onSuccessCallback = $onSuccessCallback;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getOnSuccessCallback(): ?callable
    {
        return $this->onSuccessCallback;
    }
}
