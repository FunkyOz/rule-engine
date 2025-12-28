<?php

declare(strict_types=1);

namespace RuleEngine\Expression;

use RuleEngine\Context\ContextInterface;
use RuleEngine\Operator\OperatorInterface;

final readonly class OperatorExpression implements ExpressionInterface
{
    /**
     * @param  array<int, ExpressionInterface>  $operands
     */
    public function __construct(
        private OperatorInterface $operator,
        private array $operands
    ) {
    }

    public function evaluate(ContextInterface $context): mixed
    {
        // Evaluate all operands first
        $evaluatedOperands = array_map(
            fn (ExpressionInterface $operand) => $operand->evaluate($context),
            $this->operands
        );

        // Execute the operator with evaluated values
        return $this->operator->execute($evaluatedOperands);
    }

    public function getOperator(): OperatorInterface
    {
        return $this->operator;
    }

    /**
     * @return array<int, ExpressionInterface>
     */
    public function getOperands(): array
    {
        return $this->operands;
    }

    public function __toString(): string
    {
        $operandStrings = array_map(
            fn (ExpressionInterface $op) => (string) $op,
            $this->operands
        );

        $name = $this->operator->getName();

        // Unary operator
        if (count($this->operands) === 1) {
            return sprintf('%s(%s)', $name, $operandStrings[0]);
        }

        // Binary operator (infix notation)
        if (count($this->operands) === 2) {
            return sprintf('(%s %s %s)', $operandStrings[0], $name, $operandStrings[1]);
        }

        // N-ary operator
        return sprintf('%s(%s)', $name, implode(', ', $operandStrings));
    }
}
