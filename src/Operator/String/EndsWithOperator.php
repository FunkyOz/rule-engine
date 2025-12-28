<?php

declare(strict_types=1);

namespace RuleEngine\Operator\String;

use RuleEngine\Operator\AbstractOperator;

final class EndsWithOperator extends AbstractOperator
{
    public function __construct(
        private readonly bool $caseSensitive = true
    ) {
        parent::__construct('ENDS_WITH', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        $haystack = (string) $operands[0];
        $needle = (string) $operands[1];

        if ($this->caseSensitive) {
            return str_ends_with($haystack, $needle);
        }

        return str_ends_with(
            mb_strtolower($haystack),
            mb_strtolower($needle)
        );
    }
}
