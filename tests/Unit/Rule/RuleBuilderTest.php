<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Operator\Comparison\EqualOperator;
use RuleEngine\Operator\Comparison\GreaterThanOperator;
use RuleEngine\Operator\Comparison\GreaterThanOrEqualOperator;
use RuleEngine\Operator\Comparison\LessThanOperator;
use RuleEngine\Operator\Comparison\LessThanOrEqualOperator;
use RuleEngine\Operator\Comparison\NotEqualOperator;
use RuleEngine\Operator\Comparison\StrictEqualOperator;
use RuleEngine\Operator\Comparison\StrictNotEqualOperator;
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
use RuleEngine\Operator\Set\ContainsOperator;
use RuleEngine\Operator\Set\DiffOperator;
use RuleEngine\Operator\Set\InOperator;
use RuleEngine\Operator\Set\IntersectOperator;
use RuleEngine\Operator\Set\NotInOperator;
use RuleEngine\Operator\Set\SubsetOperator;
use RuleEngine\Operator\Set\UnionOperator;
use RuleEngine\Operator\String\ContainsStringOperator;
use RuleEngine\Operator\String\EndsWithOperator;
use RuleEngine\Operator\String\MatchesOperator;
use RuleEngine\Operator\String\StartsWithOperator;
use RuleEngine\Registry\OperatorRegistry;
use RuleEngine\Rule\RuleBuilder;

function registerDefaultOperatorsForRuleBuilder(OperatorRegistry $registry): void
{
    // Comparison operators
    $registry->registerMany([
        new EqualOperator(),
        new NotEqualOperator(),
        new LessThanOperator(),
        new LessThanOrEqualOperator(),
        new GreaterThanOperator(),
        new GreaterThanOrEqualOperator(),
        new StrictEqualOperator(),
        new StrictNotEqualOperator(),
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
    ]);
}

beforeEach(function (): void {
    $this->registry = new OperatorRegistry();
    registerDefaultOperatorsForRuleBuilder($this->registry);
});

test('builds simple rule', function (): void {
    $builder = new RuleBuilder($this->registry);

    $rule = $builder
        ->name('test_rule')
        ->condition(new LiteralExpression(true))
        ->build();

    expect($rule->getName())->toBe('test_rule');
    expect($rule->evaluate(Context::fromArray([])))->toBeTrue();
});

test('throws when name is missing', function (): void {
    $builder = new RuleBuilder($this->registry);

    $builder
        ->condition(new LiteralExpression(true))
        ->build();
})->throws(InvalidArgumentException::class, 'Rule name is required');

test('throws when condition is missing', function (): void {
    $builder = new RuleBuilder($this->registry);

    $builder
        ->name('test')
        ->build();
})->throws(InvalidArgumentException::class, 'Rule condition is required');

test('sets metadata', function (): void {
    $builder = new RuleBuilder($this->registry);

    $rule = $builder
        ->name('test')
        ->condition(new LiteralExpression(true))
        ->meta('priority', 1)
        ->meta('category', 'access')
        ->build();

    expect($rule->getMeta('priority'))->toBe(1);
    expect($rule->getMeta('category'))->toBe('access');
});

test('sets metadata as array', function (): void {
    $builder = new RuleBuilder($this->registry);

    $rule = $builder
        ->name('test')
        ->condition(new LiteralExpression(true))
        ->metadata(['priority' => 1, 'category' => 'access'])
        ->build();

    expect($rule->getMeta('priority'))->toBe(1);
    expect($rule->getMeta('category'))->toBe('access');
});

test('metadata method merges with existing', function (): void {
    $builder = new RuleBuilder($this->registry);

    $rule = $builder
        ->name('test')
        ->condition(new LiteralExpression(true))
        ->meta('key1', 'value1')
        ->metadata(['key2' => 'value2', 'key3' => 'value3'])
        ->build();

    expect($rule->getMeta('key1'))->toBe('value1');
    expect($rule->getMeta('key2'))->toBe('value2');
    expect($rule->getMeta('key3'))->toBe('value3');
});

test('builds rule with fluent condition builder', function (): void {
    $builder = new RuleBuilder($this->registry);

    $rule = $builder
        ->name('age_check')
        ->when('age')->greaterThanOrEqual(18)
        ->then()
        ->build();

    $context = Context::fromArray(['age' => 25]);
    expect($rule->evaluate($context))->toBeTrue();

    $context2 = Context::fromArray(['age' => 15]);
    expect($rule->evaluate($context2))->toBeFalse();
});

test('builds rule with and conditions', function (): void {
    $builder = new RuleBuilder($this->registry);

    $rule = $builder
        ->name('verified_adult')
        ->when('age')->greaterThanOrEqual(18)
        ->andWhen('verified')->equals(true)
        ->then()
        ->build();

    // Both pass
    $context1 = Context::fromArray(['age' => 25, 'verified' => true]);
    expect($rule->evaluate($context1))->toBeTrue();

    // Age fails
    $context2 = Context::fromArray(['age' => 15, 'verified' => true]);
    expect($rule->evaluate($context2))->toBeFalse();

    // Verified fails
    $context3 = Context::fromArray(['age' => 25, 'verified' => false]);
    expect($rule->evaluate($context3))->toBeFalse();
});

test('builds rule with or conditions', function (): void {
    $builder = new RuleBuilder($this->registry);

    // Use IN operator instead of OR for this test case
    $rule = $builder
        ->name('access_rule')
        ->when('role')->in(['admin', 'moderator'])
        ->then()
        ->build();

    $admin = Context::fromArray(['role' => 'admin']);
    expect($rule->evaluate($admin))->toBeTrue();

    $moderator = Context::fromArray(['role' => 'moderator']);
    expect($rule->evaluate($moderator))->toBeTrue();

    $user = Context::fromArray(['role' => 'user']);
    expect($rule->evaluate($user))->toBeFalse();
});

test('builds rule with in operator', function (): void {
    $builder = new RuleBuilder($this->registry);

    $rule = $builder
        ->name('role_check')
        ->when('role')->in(['admin', 'moderator', 'editor'])
        ->then()
        ->build();

    $admin = Context::fromArray(['role' => 'admin']);
    expect($rule->evaluate($admin))->toBeTrue();

    $user = Context::fromArray(['role' => 'user']);
    expect($rule->evaluate($user))->toBeFalse();
});

test('builds rule with string operators', function (): void {
    $builder = new RuleBuilder($this->registry);

    $rule = $builder
        ->name('email_check')
        ->when('email')->endsWith('@company.com')
        ->then()
        ->build();

    $valid = Context::fromArray(['email' => 'john@company.com']);
    expect($rule->evaluate($valid))->toBeTrue();

    $invalid = Context::fromArray(['email' => 'john@gmail.com']);
    expect($rule->evaluate($invalid))->toBeFalse();
});

test('builds rule with complex nested conditions', function (): void {
    $builder = new RuleBuilder($this->registry);

    // Test AND conditions chaining
    $rule = $builder
        ->name('complex_access')
        ->when('age')->greaterThanOrEqual(18)
        ->andWhen('verified')->equals(true)
        ->andWhen('role')->in(['admin', 'user'])
        ->then()
        ->build();

    // All conditions pass
    $context1 = Context::fromArray(['age' => 25, 'verified' => true, 'role' => 'user']);
    expect($rule->evaluate($context1))->toBeTrue();

    // Age fails
    $context2 = Context::fromArray(['age' => 15, 'verified' => true, 'role' => 'admin']);
    expect($rule->evaluate($context2))->toBeFalse();

    // Verified fails
    $context3 = Context::fromArray(['age' => 25, 'verified' => false, 'role' => 'user']);
    expect($rule->evaluate($context3))->toBeFalse();

    // Role fails
    $context4 = Context::fromArray(['age' => 25, 'verified' => true, 'role' => 'guest']);
    expect($rule->evaluate($context4))->toBeFalse();
});

test('set condition internal method', function (): void {
    $builder = new RuleBuilder($this->registry);
    $condition = new LiteralExpression(true);

    $builder->name('test');
    $builder->setCondition($condition);

    $rule = $builder->build();

    expect($rule->getCondition())->toBe($condition);
});

test('fluent methods chaining returns builder', function (): void {
    $builder = new RuleBuilder($this->registry);

    $result1 = $builder->name('test');
    expect($result1)->toBe($builder);

    $result2 = $builder->meta('key', 'value');
    expect($result2)->toBe($builder);

    $result3 = $builder->metadata(['key2' => 'value2']);
    expect($result3)->toBe($builder);

    $result4 = $builder->condition(new LiteralExpression(true));
    expect($result4)->toBe($builder);
});
