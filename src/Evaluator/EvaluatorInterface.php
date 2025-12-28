<?php

declare(strict_types=1);

namespace RuleEngine\Evaluator;

use RuleEngine\Context\ContextInterface;
use RuleEngine\Expression\ExpressionInterface;

interface EvaluatorInterface
{
    /**
     * Evaluate an expression within a context.
     */
    public function evaluate(ExpressionInterface $expression, ContextInterface $context): mixed;
}
