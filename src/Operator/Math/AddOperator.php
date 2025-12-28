<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Math;

use RuleEngine\Operator\AbstractOperator;

final class AddOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('+', -1); // Variadic
    }

    public function execute(array $operands): int|float
    {
        $this->validateOperandCount($operands);

        return array_sum($operands);
    }
}
