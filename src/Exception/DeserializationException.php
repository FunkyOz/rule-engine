<?php

declare(strict_types=1);

namespace RuleEngine\Exception;

final class DeserializationException extends RuleEngineException
{
    public function __construct(string $message)
    {
        parent::__construct("Deserialization error: {$message}");
    }
}
