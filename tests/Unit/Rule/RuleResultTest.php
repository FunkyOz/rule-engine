<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Rule\Rule;
use RuleEngine\Rule\RuleResult;

test('passed returns true for passing rule', function (): void {
    $rule = new Rule('test', new LiteralExpression(true));
    $context = Context::fromArray([]);

    $result = new RuleResult($rule, true, $context);

    expect($result->passed())->toBeTrue();
    expect($result->failed())->toBeFalse();
});

test('failed returns true for failing rule', function (): void {
    $rule = new Rule('test', new LiteralExpression(false));
    $context = Context::fromArray([]);

    $result = new RuleResult($rule, false, $context);

    expect($result->failed())->toBeTrue();
    expect($result->passed())->toBeFalse();
});

test('get rule returns original rule', function (): void {
    $rule = new Rule('test', new LiteralExpression(true));
    $context = Context::fromArray([]);

    $result = new RuleResult($rule, true, $context);

    expect($result->getRule())->toBe($rule);
});

test('get rule name returns correct name', function (): void {
    $rule = new Rule('my_rule', new LiteralExpression(true));
    $context = Context::fromArray([]);

    $result = new RuleResult($rule, true, $context);

    expect($result->getRuleName())->toBe('my_rule');
});

test('get context returns original context', function (): void {
    $rule = new Rule('test', new LiteralExpression(true));
    $context = Context::fromArray(['key' => 'value']);

    $result = new RuleResult($rule, true, $context);

    expect($result->getContext())->toBe($context);
});

test('to array returns correct structure', function (): void {
    $metadata = ['priority' => 1, 'category' => 'access'];
    $rule = new Rule('test_rule', new LiteralExpression(true), $metadata);
    $context = Context::fromArray([]);

    $result = new RuleResult($rule, true, $context);

    $array = $result->toArray();

    expect($array)->toHaveKey('rule');
    expect($array)->toHaveKey('passed');
    expect($array)->toHaveKey('metadata');
    expect($array['rule'])->toBe('test_rule');
    expect($array['passed'])->toBeTrue();
    expect($array['metadata'])->toBe($metadata);
});

test('to array with failed rule', function (): void {
    $rule = new Rule('test_rule', new LiteralExpression(false));
    $context = Context::fromArray([]);

    $result = new RuleResult($rule, false, $context);

    $array = $result->toArray();

    expect($array['passed'])->toBeFalse();
});

test('to array with empty metadata', function (): void {
    $rule = new Rule('test_rule', new LiteralExpression(true));
    $context = Context::fromArray([]);

    $result = new RuleResult($rule, true, $context);

    $array = $result->toArray();

    expect($array['metadata'])->toBe([]);
});
