<?php

declare(strict_types=1);

namespace RuleEngine\Rule;

use RuleEngine\Expression\ExpressionInterface;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Registry\OperatorRegistryInterface;

final class ConditionBuilder
{
    private ExpressionInterface $subject;

    private ?ExpressionInterface $currentCondition = null;

    public function __construct(
        private readonly RuleBuilder $ruleBuilder,
        private readonly OperatorRegistryInterface $registry,
        string|ExpressionInterface $subject
    ) {
        $this->subject = is_string($subject)
            ? new VariableExpression($subject)
            : $subject;
    }

    // Comparison operators

    public function equals(mixed $value): self
    {
        return $this->applyOperator('=', $this->toExpression($value));
    }

    public function notEquals(mixed $value): self
    {
        return $this->applyOperator('!=', $this->toExpression($value));
    }

    public function greaterThan(mixed $value): self
    {
        return $this->applyOperator('>', $this->toExpression($value));
    }

    public function greaterThanOrEqual(mixed $value): self
    {
        return $this->applyOperator('>=', $this->toExpression($value));
    }

    public function lessThan(mixed $value): self
    {
        return $this->applyOperator('<', $this->toExpression($value));
    }

    public function lessThanOrEqual(mixed $value): self
    {
        return $this->applyOperator('<=', $this->toExpression($value));
    }

    public function identical(mixed $value): self
    {
        return $this->applyOperator('===', $this->toExpression($value));
    }

    public function notIdentical(mixed $value): self
    {
        return $this->applyOperator('!==', $this->toExpression($value));
    }

    // Set operators

    /**
     * @param  array<mixed>  $values
     */
    public function in(array $values): self
    {
        return $this->applyOperator('IN', new LiteralExpression($values));
    }

    /**
     * @param  array<mixed>  $values
     */
    public function notIn(array $values): self
    {
        return $this->applyOperator('NOT_IN', new LiteralExpression($values));
    }

    public function contains(mixed $value): self
    {
        return $this->applyOperator('CONTAINS', $this->toExpression($value));
    }

    // String operators

    public function startsWith(string $prefix): self
    {
        return $this->applyOperator('STARTS_WITH', new LiteralExpression($prefix));
    }

    public function endsWith(string $suffix): self
    {
        return $this->applyOperator('ENDS_WITH', new LiteralExpression($suffix));
    }

    public function containsString(string $substring): self
    {
        return $this->applyOperator('CONTAINS_STRING', new LiteralExpression($substring));
    }

    public function matches(string $pattern): self
    {
        return $this->applyOperator('MATCHES', new LiteralExpression($pattern));
    }

    /**
     * Concatenate the subject with one or more strings.
     *
     * @param  mixed  ...$values  Values to concatenate with the subject
     */
    public function concat(mixed ...$values): self
    {
        $expressions = [$this->subject];

        foreach ($values as $value) {
            $expressions[] = $this->toExpression($value);
        }

        $operator = $this->registry->get('CONCAT');
        $this->subject = new OperatorExpression($operator, $expressions);

        return $this;
    }

    // Logical operators

    public function and(callable $callback): self
    {
        $subBuilder = new ConditionBuilder($this->ruleBuilder, $this->registry, $this->subject);
        $callback($subBuilder);

        if ($this->currentCondition !== null && $subBuilder->currentCondition !== null) {
            $this->currentCondition = new OperatorExpression(
                $this->registry->get('AND'),
                [$this->currentCondition, $subBuilder->currentCondition]
            );
        }

        return $this;
    }

    public function or(callable $callback): self
    {
        $subBuilder = new ConditionBuilder($this->ruleBuilder, $this->registry, $this->subject);
        $callback($subBuilder);

        if ($this->currentCondition !== null && $subBuilder->currentCondition !== null) {
            $this->currentCondition = new OperatorExpression(
                $this->registry->get('OR'),
                [$this->currentCondition, $subBuilder->currentCondition]
            );
        }

        return $this;
    }

    public function andWhen(string|ExpressionInterface $subject): self
    {
        $newSubject = is_string($subject)
            ? new VariableExpression($subject)
            : $subject;

        $this->subject = $newSubject;

        return $this;
    }

    // Finalization

    /**
     * Build and return to the rule builder.
     */
    public function then(): RuleBuilder
    {
        if ($this->currentCondition !== null) {
            $this->ruleBuilder->setCondition($this->currentCondition);
        }

        return $this->ruleBuilder;
    }

    /**
     * Get the current condition expression.
     */
    public function getCondition(): ?ExpressionInterface
    {
        return $this->currentCondition;
    }

    private function applyOperator(string $name, ExpressionInterface $value): self
    {
        $operator = $this->registry->get($name);

        $newCondition = new OperatorExpression(
            $operator,
            [$this->subject, $value]
        );

        if ($this->currentCondition === null) {
            $this->currentCondition = $newCondition;
        } else {
            // Chain with AND by default
            $this->currentCondition = new OperatorExpression(
                $this->registry->get('AND'),
                [$this->currentCondition, $newCondition]
            );
        }

        return $this;
    }

    private function toExpression(mixed $value): ExpressionInterface
    {
        if ($value instanceof ExpressionInterface) {
            return $value;
        }

        // Check if it's a variable reference (starts with $)
        if (is_string($value) && str_starts_with($value, '$')) {
            return new VariableExpression(substr($value, 1));
        }

        return new LiteralExpression($value);
    }
}
