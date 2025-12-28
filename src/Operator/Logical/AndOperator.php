<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Logical;

use RuleEngine\Operator\AbstractOperator;

final class AndOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('AND', -1); // Variadic
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        foreach ($operands as $operand) {
            if (! $operand) {
                return false;
            }
        }

        return true;
    }
}
