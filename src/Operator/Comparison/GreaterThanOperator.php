<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Comparison;

use RuleEngine\Operator\AbstractOperator;

final class GreaterThanOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('>', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        return $operands[0] > $operands[1];
    }
}
