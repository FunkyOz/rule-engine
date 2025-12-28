<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Expression\LiteralExpression;

test('evaluates string value', function (): void {
    $expression = new LiteralExpression('hello');
    $context = new Context();

    expect($expression->evaluate($context))->toBe('hello');
});

test('evaluates integer value', function (): void {
    $expression = new LiteralExpression(42);
    $context = new Context();

    expect($expression->evaluate($context))->toBe(42);
});

test('evaluates float value', function (): void {
    $expression = new LiteralExpression(3.14);
    $context = new Context();

    expect($expression->evaluate($context))->toBe(3.14);
});

test('evaluates boolean value', function (): void {
    $trueExpr = new LiteralExpression(true);
    $falseExpr = new LiteralExpression(false);
    $context = new Context();

    expect($trueExpr->evaluate($context))->toBeTrue()
        ->and($falseExpr->evaluate($context))->toBeFalse();
});

test('evaluates null value', function (): void {
    $expression = new LiteralExpression(null);
    $context = new Context();

    expect($expression->evaluate($context))->toBeNull();
});

test('evaluates array value', function (): void {
    $array = [1, 2, 3];
    $expression = new LiteralExpression($array);
    $context = new Context();

    expect($expression->evaluate($context))->toBe($array);
});

test('get value returns stored value', function (): void {
    $expression = new LiteralExpression('test');

    expect($expression->getValue())->toBe('test');
});

test('to string with string', function (): void {
    $expression = new LiteralExpression('hello');

    expect((string) $expression)->toBe('"hello"');
});

test('to string with integer', function (): void {
    $expression = new LiteralExpression(42);

    expect((string) $expression)->toBe('42');
});

test('to string with float', function (): void {
    $expression = new LiteralExpression(3.14);

    expect((string) $expression)->toBe('3.14');
});

test('to string with true', function (): void {
    $expression = new LiteralExpression(true);

    expect((string) $expression)->toBe('true');
});

test('to string with false', function (): void {
    $expression = new LiteralExpression(false);

    expect((string) $expression)->toBe('false');
});

test('to string with null', function (): void {
    $expression = new LiteralExpression(null);

    expect((string) $expression)->toBe('null');
});

test('to string with simple array', function (): void {
    $expression = new LiteralExpression([1, 2, 3]);

    expect((string) $expression)->toBe('[1, 2, 3]');
});

test('to string with mixed array', function (): void {
    $expression = new LiteralExpression([1, 'hello', true, null]);

    expect((string) $expression)->toBe('[1, "hello", true, null]');
});

test('to string with string containing quotes', function (): void {
    $expression = new LiteralExpression("it's \"quoted\"");

    expect((string) $expression)->toBe('"it\\\'s \\"quoted\\""');
});
