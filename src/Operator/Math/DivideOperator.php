<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Math;

use RuleEngine\Exception\DivisionByZeroException;
use RuleEngine\Operator\AbstractOperator;

final class DivideOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('/', 2);
    }

    public function execute(array $operands): int|float
    {
        $this->validateOperandCount($operands);

        if ($operands[1] == 0) {
            throw new DivisionByZeroException();
        }

        return $operands[0] / $operands[1];
    }
}
