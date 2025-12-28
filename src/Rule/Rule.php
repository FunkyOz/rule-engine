<?php

declare(strict_types=1);

namespace RuleEngine\Rule;

use RuleEngine\Context\ContextInterface;
use RuleEngine\Expression\ExpressionInterface;

final readonly class Rule
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private string $name,
        private ExpressionInterface $condition,
        private array $metadata = []
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCondition(): ExpressionInterface
    {
        return $this->condition;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get a specific metadata value.
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Evaluate the rule condition.
     */
    public function evaluate(ContextInterface $context): bool
    {
        return (bool) $this->condition->evaluate($context);
    }

    /**
     * Evaluate and return a RuleResult with details.
     */
    public function evaluateWithResult(ContextInterface $context): RuleResult
    {
        $result = $this->evaluate($context);

        return new RuleResult(
            rule: $this,
            passed: $result,
            context: $context
        );
    }

    public function __toString(): string
    {
        return sprintf('Rule<%s>: %s', $this->name, (string) $this->condition);
    }
}
