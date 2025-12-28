<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Set;

use RuleEngine\Operator\AbstractOperator;

final class SubsetOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('SUBSET', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        $subset = $operands[0];
        $superset = $operands[1];

        if (! is_array($subset) || ! is_array($superset)) {
            return false;
        }

        return array_diff($subset, $superset) === [];
    }
}
