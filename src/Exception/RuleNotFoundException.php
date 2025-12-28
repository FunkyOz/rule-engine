<?php

declare(strict_types=1);

namespace RuleEngine\Exception;

final class RuleNotFoundException extends RuleEngineException
{
    public function __construct(string $name)
    {
        parent::__construct("Rule '{$name}' not found");
    }
}
