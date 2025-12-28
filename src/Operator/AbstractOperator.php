<?php

declare(strict_types=1);

namespace RuleEngine\Operator;

abstract class AbstractOperator implements OperatorInterface
{
    public function __construct(
        protected readonly string $name,
        protected readonly int $arity
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArity(): int
    {
        return $this->arity;
    }

    /**
     * Validate the number of operands.
     *
     * @param array<int, mixed> $operands
     * @throws \InvalidArgumentException
     */
    protected function validateOperandCount(array $operands): void
    {
        $count = count($operands);

        if ($this->arity === -1) {
            // Variadic: at least one operand
            if ($count < 1) {
                throw new \InvalidArgumentException(
                    sprintf('Operator "%s" requires at least 1 operand, %d given', $this->name, $count)
                );
            }
            return;
        }

        if ($count !== $this->arity) {
            throw new \InvalidArgumentException(
                sprintf('Operator "%s" requires %d operand(s), %d given', $this->name, $this->arity, $count)
            );
        }
    }
}
