<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Exception\VariableNotFoundException;
use RuleEngine\Expression\VariableExpression;

test('evaluates simple variable', function (): void {
    $context = Context::fromArray(['name' => 'John']);
    $expression = new VariableExpression('name');

    expect($expression->evaluate($context))->toBe('John');
});

test('evaluates nested variable', function (): void {
    $context = Context::fromArray([
        'user' => [
            'profile' => [
                'name' => 'Jane',
            ],
        ],
    ]);
    $expression = new VariableExpression('user.profile.name');

    expect($expression->evaluate($context))->toBe('Jane');
});

test('evaluates variable with integer value', function (): void {
    $context = Context::fromArray(['age' => 30]);
    $expression = new VariableExpression('age');

    expect($expression->evaluate($context))->toBe(30);
});

test('evaluates variable with array value', function (): void {
    $array = [1, 2, 3];
    $context = Context::fromArray(['numbers' => $array]);
    $expression = new VariableExpression('numbers');

    expect($expression->evaluate($context))->toBe($array);
});

test('throws exception for missing variable', function (): void {
    $context = new Context();
    $expression = new VariableExpression('missing');

    $expression->evaluate($context);
})->throws(VariableNotFoundException::class);

test('throws exception for missing nested variable', function (): void {
    $context = Context::fromArray(['user' => []]);
    $expression = new VariableExpression('user.missing');

    $expression->evaluate($context);
})->throws(VariableNotFoundException::class);

test('get name returns variable name', function (): void {
    $expression = new VariableExpression('user.name');

    expect($expression->getName())->toBe('user.name');
});

test('to string returns variable with dollar sign', function (): void {
    $expression = new VariableExpression('name');

    expect((string) $expression)->toBe('$name');
});

test('to string with nested variable', function (): void {
    $expression = new VariableExpression('user.profile.name');

    expect((string) $expression)->toBe('$user.profile.name');
});
