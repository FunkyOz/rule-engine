<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Logical;

use RuleEngine\Operator\AbstractOperator;

final class NotOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('NOT', 1);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        return !$operands[0];
    }
}
