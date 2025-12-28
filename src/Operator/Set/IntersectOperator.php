<?php

declare(strict_types=1);

namespace RuleEngine\Operator\Set;

use RuleEngine\Operator\AbstractOperator;

final class IntersectOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('INTERSECT', -1); // Variadic
    }

    /**
     * @return array<mixed>
     */
    public function execute(array $operands): array
    {
        $this->validateOperandCount($operands);

        $arrays = array_filter($operands, 'is_array');

        if (count($arrays) === 0) {
            return [];
        }

        if (count($arrays) === 1) {
            return array_values(reset($arrays));
        }

        $result = array_shift($arrays);

        foreach ($arrays as $array) {
            $result = array_intersect($result, $array);
        }

        return array_values($result);
    }
}
