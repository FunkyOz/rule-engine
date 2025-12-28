<?php

declare(strict_types=1);

namespace RuleEngine\Exception;

final class InvalidRegexException extends RuleEngineException
{
    public function __construct(string $pattern, string $error)
    {
        parent::__construct("Invalid regular expression '{$pattern}': {$error}");
    }
}
