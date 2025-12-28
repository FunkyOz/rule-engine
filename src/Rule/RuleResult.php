<?php

declare(strict_types=1);

namespace RuleEngine\Rule;

use RuleEngine\Context\ContextInterface;

final class RuleResult
{
    public function __construct(
        private readonly Rule $rule,
        private readonly bool $passed,
        private readonly ContextInterface $context
    ) {
    }

    public function getRule(): Rule
    {
        return $this->rule;
    }

    public function getRuleName(): string
    {
        return $this->rule->getName();
    }

    public function passed(): bool
    {
        return $this->passed;
    }

    public function failed(): bool
    {
        return ! $this->passed;
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'rule' => $this->rule->getName(),
            'passed' => $this->passed,
            'metadata' => $this->rule->getMetadata(),
        ];
    }
}
