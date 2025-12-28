<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Set;

use RuleEngine\Operator\AbstractOperator;

final class DiffOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('DIFF', 2);
    }

    /**
     * @return array<mixed>
     */
    public function execute(array $operands): array
    {
        $this->validateOperandCount($operands);

        $first = $operands[0];
        $second = $operands[1];

        if (! is_array($first)) {
            return [];
        }

        if (! is_array($second)) {
            return array_values($first);
        }

        return array_values(array_diff($first, $second));
    }
}
