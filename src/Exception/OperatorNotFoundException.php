<?php

declare(strict_types=1);

namespace RuleEngine\Exception;

final class OperatorNotFoundException extends RuleEngineException
{
    public function __construct(string $name)
    {
        parent::__construct("Operator '{$name}' not found in registry");
    }
}
