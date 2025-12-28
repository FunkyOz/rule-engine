<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Math;

use RuleEngine\Operator\AbstractOperator;

final class PowerOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('^', 2);
    }

    public function execute(array $operands): int|float
    {
        $this->validateOperandCount($operands);

        return $operands[0] ** $operands[1];
    }
}
