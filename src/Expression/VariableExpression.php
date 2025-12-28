<?php

declare(strict_types=1);

namespace RuleEngine\Expression;

use RuleEngine\Context\ContextInterface;

final readonly class VariableExpression implements ExpressionInterface
{
    public function __construct(
        private string $name
    ) {
    }

    public function evaluate(ContextInterface $context): mixed
    {
        return $context->get($this->name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return '$' . $this->name;
    }
}
