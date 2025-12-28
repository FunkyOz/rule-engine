<?php

declare(strict_types=1);

namespace RuleEngine\Rule;

use RuleEngine\Expression\ExpressionInterface;
use RuleEngine\Registry\OperatorRegistryInterface;

final class RuleBuilder
{
    private ?string $name = null;

    private ?ExpressionInterface $condition = null;

    /** @var array<string, mixed> */
    private array $metadata = [];

    public function __construct(
        private readonly OperatorRegistryInterface $registry
    ) {
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function meta(string $key, mixed $value): self
    {
        $this->metadata[$key] = $value;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function metadata(array $metadata): self
    {
        $this->metadata = array_merge($this->metadata, $metadata);

        return $this;
    }

    /**
     * Start building a condition.
     */
    public function when(string|ExpressionInterface $subject): ConditionBuilder
    {
        return new ConditionBuilder($this, $this->registry, $subject);
    }

    /**
     * Set the condition directly.
     */
    public function condition(ExpressionInterface $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * Build the rule.
     *
     * @throws \InvalidArgumentException
     */
    public function build(): Rule
    {
        if ($this->name === null) {
            throw new \InvalidArgumentException('Rule name is required');
        }

        if ($this->condition === null) {
            throw new \InvalidArgumentException('Rule condition is required');
        }

        return new Rule(
            name: $this->name,
            condition: $this->condition,
            metadata: $this->metadata
        );
    }

    /**
     * Set the condition from the condition builder (internal).
     */
    public function setCondition(ExpressionInterface $condition): self
    {
        $this->condition = $condition;

        return $this;
    }
}
