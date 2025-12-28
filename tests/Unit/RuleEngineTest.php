<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Exception\RuleNotFoundException;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\Comparison\EqualOperator;
use RuleEngine\Operator\Comparison\GreaterThanOrEqualOperator;
use RuleEngine\Rule\Rule;
use RuleEngine\Rule\RuleBuilder;
use RuleEngine\RuleEngine;

beforeEach(function (): void {
    $this->engine = RuleEngine::create();
});

test('creates engine with defaults', function (): void {
    $engine = RuleEngine::create();

    expect($engine)->toBeInstanceOf(RuleEngine::class);
    expect($engine->getRegistry())->not->toBeNull();
    expect($engine->getEvaluator())->not->toBeNull();
});

test('builder returns rule builder', function (): void {
    $builder = $this->engine->builder();

    expect($builder)->toBeInstanceOf(RuleBuilder::class);
});

test('adds rule', function (): void {
    $rule = new Rule('test', new LiteralExpression(true));

    $this->engine->addRule($rule);

    expect($this->engine->hasRule('test'))->toBeTrue();
    expect($this->engine->getRule('test'))->toBe($rule);
});

test('adds multiple rules', function (): void {
    $rules = [
        new Rule('rule1', new LiteralExpression(true)),
        new Rule('rule2', new LiteralExpression(false)),
    ];

    $this->engine->addRules($rules);

    expect($this->engine->hasRule('rule1'))->toBeTrue();
    expect($this->engine->hasRule('rule2'))->toBeTrue();
});

test('get rule returns null for non existent', function (): void {
    expect($this->engine->getRule('nonexistent'))->toBeNull();
});

test('has rule returns false for non existent', function (): void {
    expect($this->engine->hasRule('nonexistent'))->toBeFalse();
});

test('removes rule', function (): void {
    $rule = new Rule('test', new LiteralExpression(true));
    $this->engine->addRule($rule);

    expect($this->engine->hasRule('test'))->toBeTrue();

    $this->engine->removeRule('test');

    expect($this->engine->hasRule('test'))->toBeFalse();
});

test('evaluates rule with array context', function (): void {
    $rule = new Rule(
        'age_check',
        new OperatorExpression(
            new GreaterThanOrEqualOperator(),
            [new VariableExpression('age'), new LiteralExpression(18)]
        )
    );

    $this->engine->addRule($rule);

    expect($this->engine->evaluate('age_check', ['age' => 25]))->toBeTrue();
    expect($this->engine->evaluate('age_check', ['age' => 15]))->toBeFalse();
});

test('evaluates rule with context interface', function (): void {
    $rule = new Rule(
        'test',
        new LiteralExpression(true)
    );

    $this->engine->addRule($rule);

    $context = Context::fromArray(['key' => 'value']);

    expect($this->engine->evaluate('test', $context))->toBeTrue();
});

test('throws when evaluating non existent rule', function (): void {
    $this->engine->evaluate('nonexistent', []);
})->throws(RuleNotFoundException::class);

test('evaluate with result returns rule result', function (): void {
    $rule = new Rule('test', new LiteralExpression(true));
    $this->engine->addRule($rule);

    $result = $this->engine->evaluateWithResult('test', []);

    expect($result->passed())->toBeTrue();
    expect($result->getRuleName())->toBe('test');
});

test('evaluate all returns true when all pass', function (): void {
    $this->engine->addRule(new Rule('rule1', new LiteralExpression(true)));
    $this->engine->addRule(new Rule('rule2', new LiteralExpression(true)));

    expect($this->engine->evaluateAll([]))->toBeTrue();
});

test('evaluate all returns false when any fails', function (): void {
    $this->engine->addRule(new Rule('rule1', new LiteralExpression(true)));
    $this->engine->addRule(new Rule('rule2', new LiteralExpression(false)));

    expect($this->engine->evaluateAll([]))->toBeFalse();
});

test('evaluate any returns true when any passes', function (): void {
    $this->engine->addRule(new Rule('rule1', new LiteralExpression(false)));
    $this->engine->addRule(new Rule('rule2', new LiteralExpression(true)));

    expect($this->engine->evaluateAny([]))->toBeTrue();
});

test('evaluate any returns false when all fail', function (): void {
    $this->engine->addRule(new Rule('rule1', new LiteralExpression(false)));
    $this->engine->addRule(new Rule('rule2', new LiteralExpression(false)));

    expect($this->engine->evaluateAny([]))->toBeFalse();
});

test('evaluate all with results returns all results', function (): void {
    $this->engine->addRule(new Rule('rule1', new LiteralExpression(true)));
    $this->engine->addRule(new Rule('rule2', new LiteralExpression(false)));

    $results = $this->engine->evaluateAllWithResults([]);

    expect($results)->toHaveCount(2);
    expect($results['rule1']->passed())->toBeTrue();
    expect($results['rule2']->passed())->toBeFalse();
});

test('get passing rules returns only passing rules', function (): void {
    $this->engine->addRule(new Rule('pass1', new LiteralExpression(true)));
    $this->engine->addRule(new Rule('fail1', new LiteralExpression(false)));
    $this->engine->addRule(new Rule('pass2', new LiteralExpression(true)));

    $passing = $this->engine->getPassingRules([]);

    expect($passing)->toHaveCount(2);
    expect($passing)->toHaveKey('pass1');
    expect($passing)->toHaveKey('pass2');
});

test('get failing rules returns only failing rules', function (): void {
    $this->engine->addRule(new Rule('pass1', new LiteralExpression(true)));
    $this->engine->addRule(new Rule('fail1', new LiteralExpression(false)));
    $this->engine->addRule(new Rule('fail2', new LiteralExpression(false)));

    $failing = $this->engine->getFailingRules([]);

    expect($failing)->toHaveCount(2);
    expect($failing)->toHaveKey('fail1');
    expect($failing)->toHaveKey('fail2');
});

test('evaluate expression directly', function (): void {
    $expression = new OperatorExpression(
        new \RuleEngine\Operator\Math\AddOperator(),
        [new LiteralExpression(5), new LiteralExpression(3)]
    );

    $result = $this->engine->evaluateExpression($expression, []);

    expect($result)->toBe(8);
});

test('registers custom operator', function (): void {
    $operator = new EqualOperator();

    $this->engine->registerOperator($operator);

    expect($this->engine->getRegistry()->get('='))->toBe($operator);
});

test('get rules returns all rules', function (): void {
    $rule1 = new Rule('rule1', new LiteralExpression(true));
    $rule2 = new Rule('rule2', new LiteralExpression(false));

    $this->engine->addRule($rule1);
    $this->engine->addRule($rule2);

    $rules = $this->engine->getRules();

    expect($rules)->toHaveCount(2);
    expect($rules)->toHaveKey('rule1');
    expect($rules)->toHaveKey('rule2');
});

test('fluent rule building', function (): void {
    $rule = $this->engine->builder()
        ->name('age_check')
        ->when('age')->greaterThanOrEqual(18)
        ->then()
        ->build();

    $this->engine->addRule($rule);

    expect($this->engine->evaluate('age_check', ['age' => 25]))->toBeTrue();
    expect($this->engine->evaluate('age_check', ['age' => 15]))->toBeFalse();
});

test('complex rule with builder', function (): void {
    $rule = $this->engine->builder()
        ->name('access_rule')
        ->when('age')->greaterThanOrEqual(18)
        ->andWhen('verified')->equals(true)
        ->then()
        ->meta('priority', 1)
        ->build();

    $this->engine->addRule($rule);

    // Both conditions pass
    expect($this->engine->evaluate('access_rule', [
        'age' => 25,
        'verified' => true,
    ]))->toBeTrue();

    // Age fails
    expect($this->engine->evaluate('access_rule', [
        'age' => 15,
        'verified' => true,
    ]))->toBeFalse();

    // Verified fails
    expect($this->engine->evaluate('access_rule', [
        'age' => 25,
        'verified' => false,
    ]))->toBeFalse();
});

test('rule with metadata', function (): void {
    $rule = $this->engine->builder()
        ->name('test')
        ->when('value')->equals(10)
        ->then()
        ->meta('category', 'validation')
        ->meta('priority', 5)
        ->build();

    $this->engine->addRule($rule);

    $retrievedRule = $this->engine->getRule('test');

    expect($retrievedRule->getMeta('category'))->toBe('validation');
    expect($retrievedRule->getMeta('priority'))->toBe(5);
});

test('default operators are registered', function (): void {
    $registry = $this->engine->getRegistry();

    // Test a few operators to ensure defaults are loaded
    expect($registry->get('='))->not->toBeNull();
    expect($registry->get('>'))->not->toBeNull();
    expect($registry->get('AND'))->not->toBeNull();
    expect($registry->get('+'))->not->toBeNull();
    expect($registry->get('IN'))->not->toBeNull();
    expect($registry->get('STARTS_WITH'))->not->toBeNull();
});

test('method chaining returns engine', function (): void {
    $result1 = $this->engine->addRule(new Rule('test1', new LiteralExpression(true)));
    expect($result1)->toBe($this->engine);

    $result2 = $this->engine->addRules([new Rule('test2', new LiteralExpression(true))]);
    expect($result2)->toBe($this->engine);

    $result3 = $this->engine->removeRule('test1');
    expect($result3)->toBe($this->engine);

    $result4 = $this->engine->registerOperator(new EqualOperator());
    expect($result4)->toBe($this->engine);
});

test('evaluate with nested context data', function (): void {
    $rule = $this->engine->builder()
        ->name('nested_check')
        ->when('user.profile.age')->greaterThanOrEqual(18)
        ->then()
        ->build();

    $this->engine->addRule($rule);

    $context = [
        'user' => [
            'profile' => [
                'age' => 25,
            ],
        ],
    ];

    expect($this->engine->evaluate('nested_check', $context))->toBeTrue();
});

test('evaluate with set operators', function (): void {
    $rule = $this->engine->builder()
        ->name('role_check')
        ->when('role')->in(['admin', 'moderator', 'editor'])
        ->then()
        ->build();

    $this->engine->addRule($rule);

    expect($this->engine->evaluate('role_check', ['role' => 'admin']))->toBeTrue();
    expect($this->engine->evaluate('role_check', ['role' => 'user']))->toBeFalse();
});

test('evaluate with string operators', function (): void {
    $rule = $this->engine->builder()
        ->name('email_check')
        ->when('email')->endsWith('@company.com')
        ->then()
        ->build();

    $this->engine->addRule($rule);

    expect($this->engine->evaluate('email_check', ['email' => 'john@company.com']))->toBeTrue();
    expect($this->engine->evaluate('email_check', ['email' => 'john@gmail.com']))->toBeFalse();
});
