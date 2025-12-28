<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Set;

use RuleEngine\Operator\AbstractOperator;

final class NotInOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('NOT_IN', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        $needle = $operands[0];
        $haystack = $operands[1];

        if (! is_array($haystack)) {
            return true;
        }

        return ! in_array($needle, $haystack, strict: true);
    }
}
