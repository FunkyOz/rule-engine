<?php

declare(strict_types=1);

namespace RuleEngine\Operator\String;

use RuleEngine\Operator\AbstractOperator;

final class ConcatOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('CONCAT', -1); // Variadic: accepts 2+ operands
    }

    public function execute(array $operands): string
    {
        $this->validateOperandCount($operands);

        // Cast all operands to strings and concatenate
        $result = '';
        foreach ($operands as $operand) {
            $result .= (string) $operand;
        }

        return $result;
    }
}
