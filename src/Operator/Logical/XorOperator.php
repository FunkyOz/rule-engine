<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Logical;

use RuleEngine\Operator\AbstractOperator;

final class XorOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('XOR', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        return (bool) $operands[0] xor (bool) $operands[1];
    }
}
