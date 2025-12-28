<?php

declare(strict_types=1);

namespace RuleEngine\Exception;

final class DivisionByZeroException extends RuleEngineException
{
    public function __construct()
    {
        parent::__construct('Division by zero');
    }
}
