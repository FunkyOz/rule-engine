<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\Comparison\EqualOperator;
use RuleEngine\Operator\Comparison\GreaterThanOrEqualOperator;
use RuleEngine\Operator\Logical\AndOperator;
use RuleEngine\Rule\Rule;

test('evaluates to true when condition passes', function (): void {
    $condition = new OperatorExpression(
        new GreaterThanOrEqualOperator(),
        [new VariableExpression('age'), new LiteralExpression(18)]
    );

    $rule = new Rule('adult', $condition);
    $context = Context::fromArray(['age' => 25]);

    expect($rule->evaluate($context))->toBeTrue();
});

test('evaluates to false when condition fails', function (): void {
    $condition = new OperatorExpression(
        new GreaterThanOrEqualOperator(),
        [new VariableExpression('age'), new LiteralExpression(18)]
    );

    $rule = new Rule('adult', $condition);
    $context = Context::fromArray(['age' => 15]);

    expect($rule->evaluate($context))->toBeFalse();
});

test('returns rule result with details', function (): void {
    $condition = new LiteralExpression(true);
    $rule = new Rule('test', $condition, ['priority' => 1]);
    $context = Context::fromArray([]);

    $result = $rule->evaluateWithResult($context);

    expect($result->passed())->toBeTrue();
    expect($result->getRuleName())->toBe('test');
    expect($result->getRule())->toBe($rule);
});

test('preserves metadata', function (): void {
    $rule = new Rule('test', new LiteralExpression(true), [
        'category' => 'access',
        'priority' => 1,
    ]);

    expect($rule->getMeta('category'))->toBe('access');
    expect($rule->getMeta('priority'))->toBe(1);
    expect($rule->getMeta('missing', 'default'))->toBe('default');
});

test('getters return correct values', function (): void {
    $condition = new LiteralExpression(true);
    $metadata = ['key' => 'value'];

    $rule = new Rule('test_rule', $condition, $metadata);

    expect($rule->getName())->toBe('test_rule');
    expect($rule->getCondition())->toBe($condition);
    expect($rule->getMetadata())->toBe($metadata);
});

test('to string formats rule correctly', function (): void {
    $condition = new LiteralExpression(42);
    $rule = new Rule('test_rule', $condition);

    $string = (string) $rule;

    expect($string)->toContain('Rule<test_rule>');
    expect($string)->toContain('42');
});

test('evaluate with complex condition', function (): void {
    // Test nested condition: age >= 18 AND verified == true
    $ageCheck = new OperatorExpression(
        new GreaterThanOrEqualOperator(),
        [new VariableExpression('age'), new LiteralExpression(18)]
    );

    $verifiedCheck = new VariableExpression('verified');

    $condition = new OperatorExpression(
        new AndOperator(),
        [$ageCheck, $verifiedCheck]
    );

    $rule = new Rule('verified_adult', $condition);

    // Both conditions pass
    $context1 = Context::fromArray(['age' => 25, 'verified' => true]);
    expect($rule->evaluate($context1))->toBeTrue();

    // Age fails
    $context2 = Context::fromArray(['age' => 15, 'verified' => true]);
    expect($rule->evaluate($context2))->toBeFalse();

    // Verified fails
    $context3 = Context::fromArray(['age' => 25, 'verified' => false]);
    expect($rule->evaluate($context3))->toBeFalse();
});

test('evaluate with nested object access', function (): void {
    $condition = new OperatorExpression(
        new EqualOperator(),
        [new VariableExpression('user.role'), new LiteralExpression('admin')]
    );

    $rule = new Rule('is_admin', $condition);

    $context = Context::fromArray([
        'user' => [
            'role' => 'admin',
        ],
    ]);

    expect($rule->evaluate($context))->toBeTrue();
});
