<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\Comparison\GreaterThanOperator;
use RuleEngine\Rule\Rule;
use RuleEngine\Rule\RuleSet;

test('add adds rule to set', function (): void {
    $ruleSet = new RuleSet();
    $rule = new Rule('test', new LiteralExpression(true));

    $ruleSet->add($rule);

    expect($ruleSet->has('test'))->toBeTrue();
    expect($ruleSet->count())->toBe(1);
});

test('add many adds multiple rules', function (): void {
    $ruleSet = new RuleSet();
    $rules = [
        new Rule('rule1', new LiteralExpression(true)),
        new Rule('rule2', new LiteralExpression(false)),
        new Rule('rule3', new LiteralExpression(true)),
    ];

    $ruleSet->addMany($rules);

    expect($ruleSet->count())->toBe(3);
    expect($ruleSet->has('rule1'))->toBeTrue();
    expect($ruleSet->has('rule2'))->toBeTrue();
    expect($ruleSet->has('rule3'))->toBeTrue();
});

test('get returns correct rule', function (): void {
    $ruleSet = new RuleSet();
    $rule = new Rule('test', new LiteralExpression(true));

    $ruleSet->add($rule);

    expect($ruleSet->get('test'))->toBe($rule);
});

test('get returns null for non existent rule', function (): void {
    $ruleSet = new RuleSet();

    expect($ruleSet->get('nonexistent'))->toBeNull();
});

test('has returns false for non existent rule', function (): void {
    $ruleSet = new RuleSet();

    expect($ruleSet->has('nonexistent'))->toBeFalse();
});

test('remove removes rule', function (): void {
    $ruleSet = new RuleSet();
    $rule = new Rule('test', new LiteralExpression(true));

    $ruleSet->add($rule);
    expect($ruleSet->has('test'))->toBeTrue();

    $ruleSet->remove('test');
    expect($ruleSet->has('test'))->toBeFalse();
    expect($ruleSet->count())->toBe(0);
});

test('all returns all rules', function (): void {
    $ruleSet = new RuleSet();
    $rule1 = new Rule('rule1', new LiteralExpression(true));
    $rule2 = new Rule('rule2', new LiteralExpression(false));

    $ruleSet->add($rule1);
    $ruleSet->add($rule2);

    $all = $ruleSet->all();

    expect($all)->toHaveCount(2);
    expect($all)->toHaveKey('rule1');
    expect($all)->toHaveKey('rule2');
    expect($all['rule1'])->toBe($rule1);
    expect($all['rule2'])->toBe($rule2);
});

test('evaluate all returns true when all rules pass', function (): void {
    $ruleSet = new RuleSet();
    $ruleSet->add(new Rule('rule1', new LiteralExpression(true)));
    $ruleSet->add(new Rule('rule2', new LiteralExpression(true)));
    $ruleSet->add(new Rule('rule3', new LiteralExpression(true)));

    $context = Context::fromArray([]);

    expect($ruleSet->evaluateAll($context))->toBeTrue();
});

test('evaluate all returns false when any rule fails', function (): void {
    $ruleSet = new RuleSet();
    $ruleSet->add(new Rule('rule1', new LiteralExpression(true)));
    $ruleSet->add(new Rule('rule2', new LiteralExpression(false)));
    $ruleSet->add(new Rule('rule3', new LiteralExpression(true)));

    $context = Context::fromArray([]);

    expect($ruleSet->evaluateAll($context))->toBeFalse();
});

test('evaluate all returns true for empty rule set', function (): void {
    $ruleSet = new RuleSet();
    $context = Context::fromArray([]);

    expect($ruleSet->evaluateAll($context))->toBeTrue();
});

test('evaluate any returns true when any rule passes', function (): void {
    $ruleSet = new RuleSet();
    $ruleSet->add(new Rule('rule1', new LiteralExpression(false)));
    $ruleSet->add(new Rule('rule2', new LiteralExpression(true)));
    $ruleSet->add(new Rule('rule3', new LiteralExpression(false)));

    $context = Context::fromArray([]);

    expect($ruleSet->evaluateAny($context))->toBeTrue();
});

test('evaluate any returns false when all rules fail', function (): void {
    $ruleSet = new RuleSet();
    $ruleSet->add(new Rule('rule1', new LiteralExpression(false)));
    $ruleSet->add(new Rule('rule2', new LiteralExpression(false)));

    $context = Context::fromArray([]);

    expect($ruleSet->evaluateAny($context))->toBeFalse();
});

test('evaluate any returns false for empty rule set', function (): void {
    $ruleSet = new RuleSet();
    $context = Context::fromArray([]);

    expect($ruleSet->evaluateAny($context))->toBeFalse();
});

test('evaluate with results returns all results', function (): void {
    $ruleSet = new RuleSet();
    $ruleSet->add(new Rule('rule1', new LiteralExpression(true)));
    $ruleSet->add(new Rule('rule2', new LiteralExpression(false)));

    $context = Context::fromArray([]);

    $results = $ruleSet->evaluateWithResults($context);

    expect($results)->toHaveCount(2);
    expect($results['rule1']->passed())->toBeTrue();
    expect($results['rule2']->passed())->toBeFalse();
});

test('get passing rules returns only passing rules', function (): void {
    $ruleSet = new RuleSet();
    $ruleSet->add(new Rule('pass1', new LiteralExpression(true)));
    $ruleSet->add(new Rule('fail1', new LiteralExpression(false)));
    $ruleSet->add(new Rule('pass2', new LiteralExpression(true)));

    $context = Context::fromArray([]);

    $passing = $ruleSet->getPassingRules($context);

    expect($passing)->toHaveCount(2);
    expect($passing)->toHaveKey('pass1');
    expect($passing)->toHaveKey('pass2');
    expect($passing)->not->toHaveKey('fail1');
});

test('get failing rules returns only failing rules', function (): void {
    $ruleSet = new RuleSet();
    $ruleSet->add(new Rule('pass1', new LiteralExpression(true)));
    $ruleSet->add(new Rule('fail1', new LiteralExpression(false)));
    $ruleSet->add(new Rule('fail2', new LiteralExpression(false)));

    $context = Context::fromArray([]);

    $failing = $ruleSet->getFailingRules($context);

    expect($failing)->toHaveCount(2);
    expect($failing)->toHaveKey('fail1');
    expect($failing)->toHaveKey('fail2');
    expect($failing)->not->toHaveKey('pass1');
});

test('rule set with context dependent rules', function (): void {
    $ruleSet = new RuleSet();

    $rule1 = new Rule(
        'age_check',
        new OperatorExpression(
            new GreaterThanOperator(),
            [new VariableExpression('age'), new LiteralExpression(18)]
        )
    );

    $rule2 = new Rule(
        'value_check',
        new OperatorExpression(
            new GreaterThanOperator(),
            [new VariableExpression('value'), new LiteralExpression(100)]
        )
    );

    $ruleSet->add($rule1);
    $ruleSet->add($rule2);

    // Both pass
    $context1 = Context::fromArray(['age' => 25, 'value' => 150]);
    expect($ruleSet->evaluateAll($context1))->toBeTrue();

    // One fails
    $context2 = Context::fromArray(['age' => 15, 'value' => 150]);
    expect($ruleSet->evaluateAll($context2))->toBeFalse();
    expect($ruleSet->evaluateAny($context2))->toBeTrue();

    // Both fail
    $context3 = Context::fromArray(['age' => 15, 'value' => 50]);
    expect($ruleSet->evaluateAll($context3))->toBeFalse();
    expect($ruleSet->evaluateAny($context3))->toBeFalse();
});

test('add replaces rule with same name', function (): void {
    $ruleSet = new RuleSet();
    $rule1 = new Rule('test', new LiteralExpression(true));
    $rule2 = new Rule('test', new LiteralExpression(false));

    $ruleSet->add($rule1);
    expect($ruleSet->get('test'))->toBe($rule1);

    $ruleSet->add($rule2);
    expect($ruleSet->get('test'))->toBe($rule2);
    expect($ruleSet->count())->toBe(1);
});
