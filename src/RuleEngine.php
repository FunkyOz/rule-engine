<?php

declare(strict_types=1);

namespace RuleEngine;

use RuleEngine\Context\Context;
use RuleEngine\Context\ContextInterface;
use RuleEngine\Evaluator\Evaluator;
use RuleEngine\Evaluator\EvaluatorInterface;
use RuleEngine\Expression\ExpressionInterface;
use RuleEngine\Operator\Comparison\EqualOperator;
use RuleEngine\Operator\Comparison\GreaterThanOperator;
use RuleEngine\Operator\Comparison\GreaterThanOrEqualOperator;
use RuleEngine\Operator\Comparison\IdenticalOperator;
use RuleEngine\Operator\Comparison\LessThanOperator;
use RuleEngine\Operator\Comparison\LessThanOrEqualOperator;
use RuleEngine\Operator\Comparison\NotEqualOperator;
use RuleEngine\Operator\Comparison\NotIdenticalOperator;
use RuleEngine\Operator\Logical\AndOperator;
use RuleEngine\Operator\Logical\NotOperator;
use RuleEngine\Operator\Logical\OrOperator;
use RuleEngine\Operator\Logical\XorOperator;
use RuleEngine\Operator\Math\AddOperator;
use RuleEngine\Operator\Math\DivideOperator;
use RuleEngine\Operator\Math\ModuloOperator;
use RuleEngine\Operator\Math\MultiplyOperator;
use RuleEngine\Operator\Math\PowerOperator;
use RuleEngine\Operator\Math\SubtractOperator;
use RuleEngine\Operator\OperatorInterface;
use RuleEngine\Operator\Set\ContainsOperator;
use RuleEngine\Operator\Set\DiffOperator;
use RuleEngine\Operator\Set\InOperator;
use RuleEngine\Operator\Set\IntersectOperator;
use RuleEngine\Operator\Set\NotInOperator;
use RuleEngine\Operator\Set\SubsetOperator;
use RuleEngine\Operator\Set\UnionOperator;
use RuleEngine\Operator\String\ConcatOperator;
use RuleEngine\Operator\String\ContainsStringOperator;
use RuleEngine\Operator\String\EndsWithOperator;
use RuleEngine\Operator\String\MatchesOperator;
use RuleEngine\Operator\String\StartsWithOperator;
use RuleEngine\Registry\OperatorRegistry;
use RuleEngine\Registry\OperatorRegistryInterface;
use RuleEngine\Rule\Rule;
use RuleEngine\Rule\RuleBuilder;
use RuleEngine\Rule\RuleResult;
use RuleEngine\Rule\RuleSet;

final class RuleEngine
{
    private OperatorRegistryInterface $registry;

    private EvaluatorInterface $evaluator;

    private RuleSet $rules;

    public function __construct(
        ?OperatorRegistryInterface $registry = null,
        ?EvaluatorInterface $evaluator = null
    ) {
        $this->registry = $registry ?? $this->createDefaultRegistry();
        $this->evaluator = $evaluator ?? new Evaluator();
        $this->rules = new RuleSet();
    }

    /**
     * Create a rule engine with default operators.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Get a RuleBuilder for fluent rule construction.
     */
    public function builder(): RuleBuilder
    {
        return new RuleBuilder($this->registry);
    }

    /**
     * Add a rule to the engine.
     */
    public function addRule(Rule $rule): self
    {
        $this->rules->add($rule);

        return $this;
    }

    /**
     * Add multiple rules to the engine.
     *
     * @param  array<Rule>  $rules
     */
    public function addRules(array $rules): self
    {
        $this->rules->addMany($rules);

        return $this;
    }

    /**
     * Get a rule by name.
     */
    public function getRule(string $name): ?Rule
    {
        return $this->rules->get($name);
    }

    /**
     * Check if a rule exists.
     */
    public function hasRule(string $name): bool
    {
        return $this->rules->has($name);
    }

    /**
     * Remove a rule by name.
     */
    public function removeRule(string $name): self
    {
        $this->rules->remove($name);

        return $this;
    }

    /**
     * Evaluate a single rule by name.
     *
     * @param  array<string, mixed>|ContextInterface  $context
     */
    public function evaluate(string $ruleName, array|ContextInterface $context): bool
    {
        $rule = $this->rules->get($ruleName);

        if ($rule === null) {
            throw new Exception\RuleNotFoundException($ruleName);
        }

        $ctx = $this->normalizeContext($context);

        return $rule->evaluate($ctx);
    }

    /**
     * Evaluate a single rule and get detailed result.
     *
     * @param  array<string, mixed>|ContextInterface  $context
     */
    public function evaluateWithResult(string $ruleName, array|ContextInterface $context): RuleResult
    {
        $rule = $this->rules->get($ruleName);

        if ($rule === null) {
            throw new Exception\RuleNotFoundException($ruleName);
        }

        $ctx = $this->normalizeContext($context);

        return $rule->evaluateWithResult($ctx);
    }

    /**
     * Evaluate all rules and return true if all pass.
     *
     * @param  array<string, mixed>|ContextInterface  $context
     */
    public function evaluateAll(array|ContextInterface $context): bool
    {
        $ctx = $this->normalizeContext($context);

        return $this->rules->evaluateAll($ctx);
    }

    /**
     * Evaluate all rules and return true if any pass.
     *
     * @param  array<string, mixed>|ContextInterface  $context
     */
    public function evaluateAny(array|ContextInterface $context): bool
    {
        $ctx = $this->normalizeContext($context);

        return $this->rules->evaluateAny($ctx);
    }

    /**
     * Evaluate all rules and get detailed results.
     *
     * @param  array<string, mixed>|ContextInterface  $context
     * @return array<RuleResult>
     */
    public function evaluateAllWithResults(array|ContextInterface $context): array
    {
        $ctx = $this->normalizeContext($context);

        return $this->rules->evaluateWithResults($ctx);
    }

    /**
     * Get rules that pass for the given context.
     *
     * @param  array<string, mixed>|ContextInterface  $context
     * @return array<Rule>
     */
    public function getPassingRules(array|ContextInterface $context): array
    {
        $ctx = $this->normalizeContext($context);

        return $this->rules->getPassingRules($ctx);
    }

    /**
     * Get rules that fail for the given context.
     *
     * @param  array<string, mixed>|ContextInterface  $context
     * @return array<Rule>
     */
    public function getFailingRules(array|ContextInterface $context): array
    {
        $ctx = $this->normalizeContext($context);

        return $this->rules->getFailingRules($ctx);
    }

    /**
     * Evaluate an expression directly.
     *
     * @param  array<string, mixed>|ContextInterface  $context
     */
    public function evaluateExpression(ExpressionInterface $expression, array|ContextInterface $context): mixed
    {
        $ctx = $this->normalizeContext($context);

        return $this->evaluator->evaluate($expression, $ctx);
    }

    /**
     * Register a custom operator.
     */
    public function registerOperator(OperatorInterface $operator): self
    {
        $this->registry->register($operator);

        return $this;
    }

    /**
     * Get the operator registry.
     */
    public function getRegistry(): OperatorRegistryInterface
    {
        return $this->registry;
    }

    /**
     * Get the evaluator.
     */
    public function getEvaluator(): EvaluatorInterface
    {
        return $this->evaluator;
    }

    /**
     * Get all registered rules.
     *
     * @return array<string, Rule>
     */
    public function getRules(): array
    {
        return $this->rules->all();
    }

    /**
     * @param  array<string, mixed>|ContextInterface  $context
     */
    private function normalizeContext(array|ContextInterface $context): ContextInterface
    {
        if ($context instanceof ContextInterface) {
            return $context;
        }

        return Context::fromArray($context);
    }

    private function createDefaultRegistry(): OperatorRegistryInterface
    {
        $registry = new OperatorRegistry();

        // Comparison operators
        $registry->registerMany([
            new EqualOperator(),
            new NotEqualOperator(),
            new LessThanOperator(),
            new LessThanOrEqualOperator(),
            new GreaterThanOperator(),
            new GreaterThanOrEqualOperator(),
            new IdenticalOperator(),
            new NotIdenticalOperator(),
        ]);

        // Logical operators
        $registry->registerMany([
            new AndOperator(),
            new OrOperator(),
            new NotOperator(),
            new XorOperator(),
        ]);

        // Math operators
        $registry->registerMany([
            new AddOperator(),
            new SubtractOperator(),
            new MultiplyOperator(),
            new DivideOperator(),
            new ModuloOperator(),
            new PowerOperator(),
        ]);

        // Set operators
        $registry->registerMany([
            new InOperator(),
            new NotInOperator(),
            new ContainsOperator(),
            new SubsetOperator(),
            new UnionOperator(),
            new IntersectOperator(),
            new DiffOperator(),
        ]);

        // String operators
        $registry->registerMany([
            new StartsWithOperator(),
            new EndsWithOperator(),
            new ContainsStringOperator(),
            new MatchesOperator(),
            new ConcatOperator(),
        ]);

        return $registry;
    }
}
