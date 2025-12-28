<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Logical;

use RuleEngine\Operator\AbstractOperator;

final class OrOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('OR', -1); // Variadic
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        foreach ($operands as $operand) {
            if ($operand) {
                return true;
            }
        }

        return false;
    }
}
