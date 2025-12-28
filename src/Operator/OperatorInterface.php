<?php

declare(strict_types=1);

namespace RuleEngine\Operator;

interface OperatorInterface
{
    /**
     * Get the operator symbol/name (e.g., '=', 'AND', '+', 'IN').
     */
    public function getName(): string;

    /**
     * Get the number of operands this operator requires.
     * Return -1 for variadic operators.
     */
    public function getArity(): int;

    /**
     * Execute the operator with the given evaluated operand values.
     *
     * @param  array<int, mixed>  $operands  The evaluated operand values
     */
    public function execute(array $operands): mixed;
}
