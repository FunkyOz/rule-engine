<?php

declare(strict_types=1);

namespace RuleEngine\Operator\String;

use RuleEngine\Operator\AbstractOperator;

final class ContainsStringOperator extends AbstractOperator
{
    public function __construct(
        private readonly bool $caseSensitive = true
    ) {
        parent::__construct('CONTAINS_STRING', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        $haystack = (string) $operands[0];
        $needle = (string) $operands[1];

        if ($this->caseSensitive) {
            return str_contains($haystack, $needle);
        }

        return str_contains(
            mb_strtolower($haystack),
            mb_strtolower($needle)
        );
    }
}
