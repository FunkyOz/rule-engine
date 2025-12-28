<?php

declare(strict_types=1);

namespace RuleEngine\Exception;

final class VariableNotFoundException extends RuleEngineException
{
    public function __construct(string $name)
    {
        parent::__construct("Variable '{$name}' not found in context");
    }
}
