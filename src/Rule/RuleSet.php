<?php

declare(strict_types=1);

namespace RuleEngine\Rule;

use RuleEngine\Context\ContextInterface;

final class RuleSet
{
    /**
     * @var array<string, Rule>
     */
    private array $rules = [];

    public function add(Rule $rule): self
    {
        $this->rules[$rule->getName()] = $rule;

        return $this;
    }

    /**
     * @param array<Rule> $rules
     */
    public function addMany(array $rules): self
    {
        foreach ($rules as $rule) {
            $this->add($rule);
        }

        return $this;
    }

    public function get(string $name): ?Rule
    {
        return $this->rules[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->rules[$name]);
    }

    public function remove(string $name): self
    {
        unset($this->rules[$name]);

        return $this;
    }

    /**
     * @return array<string, Rule>
     */
    public function all(): array
    {
        return $this->rules;
    }

    public function count(): int
    {
        return count($this->rules);
    }

    /**
     * Evaluate all rules and return true if ALL pass.
     */
    public function evaluateAll(ContextInterface $context): bool
    {
        foreach ($this->rules as $rule) {
            if (!$rule->evaluate($context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate all rules and return true if ANY passes.
     */
    public function evaluateAny(ContextInterface $context): bool
    {
        foreach ($this->rules as $rule) {
            if ($rule->evaluate($context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Evaluate all rules and return detailed results.
     *
     * @return array<RuleResult>
     */
    public function evaluateWithResults(ContextInterface $context): array
    {
        return array_map(
            fn (Rule $rule) => $rule->evaluateWithResult($context),
            $this->rules
        );
    }

    /**
     * Get only the rules that passed.
     *
     * @return array<Rule>
     */
    public function getPassingRules(ContextInterface $context): array
    {
        return array_filter(
            $this->rules,
            fn (Rule $rule) => $rule->evaluate($context)
        );
    }

    /**
     * Get only the rules that failed.
     *
     * @return array<Rule>
     */
    public function getFailingRules(ContextInterface $context): array
    {
        return array_filter(
            $this->rules,
            fn (Rule $rule) => !$rule->evaluate($context)
        );
    }
}
