<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Set;

use RuleEngine\Operator\AbstractOperator;

final class UnionOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('UNION', -1); // Variadic
    }

    /**
     * @return array<mixed>
     */
    public function execute(array $operands): array
    {
        $this->validateOperandCount($operands);

        $result = [];

        foreach ($operands as $operand) {
            if (is_array($operand)) {
                $result = array_merge($result, $operand);
            }
        }

        return array_values(array_unique($result, SORT_REGULAR));
    }
}
