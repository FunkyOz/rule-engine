<?php

declare(strict_types=1);

namespace RuleEngine\Expression;

use RuleEngine\Context\ContextInterface;

interface ExpressionInterface
{
    /**
     * Evaluate the expression and return its value.
     */
    public function evaluate(ContextInterface $context): mixed;

    /**
     * Return a string representation for debugging.
     */
    public function __toString(): string;
}
