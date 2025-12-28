<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Set;

use RuleEngine\Operator\AbstractOperator;

final class ContainsOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('CONTAINS', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        $haystack = $operands[0];
        $needle = $operands[1];

        if (!is_array($haystack)) {
            return false;
        }

        return in_array($needle, $haystack, strict: true);
    }
}
