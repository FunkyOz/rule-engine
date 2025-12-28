<?php

declare(strict_types=1);

namespace RuleEngine\Evaluator;

use RuleEngine\Context\ContextInterface;
use RuleEngine\Expression\ExpressionInterface;

final class Evaluator implements EvaluatorInterface
{
    public function evaluate(ExpressionInterface $expression, ContextInterface $context): mixed
    {
        return $expression->evaluate($context);
    }

    /**
     * Evaluate an expression and cast the result to boolean.
     */
    public function evaluateAsBoolean(ExpressionInterface $expression, ContextInterface $context): bool
    {
        return (bool) $this->evaluate($expression, $context);
    }

    /**
     * Evaluate multiple expressions and return all results.
     *
     * @param  array<ExpressionInterface>  $expressions
     * @return array<mixed>
     */
    public function evaluateAll(array $expressions, ContextInterface $context): array
    {
        return array_map(
            fn (ExpressionInterface $expr) => $this->evaluate($expr, $context),
            $expressions
        );
    }
}
