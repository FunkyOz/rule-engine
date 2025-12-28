<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Exception\VariableNotFoundException;

test('gets and sets simple values', function (): void {
    $context = new Context();
    $context->set('name', 'John');

    expect($context->get('name'))->toBe('John')
        ->and($context->has('name'))->toBeTrue();
});

test('supports dot notation for nested access', function (): void {
    $context = Context::fromArray([
        'user' => [
            'profile' => [
                'name' => 'Jane',
            ],
        ],
    ]);

    expect($context->get('user.profile.name'))->toBe('Jane');
});

test('throws variable not found exception for missing variables', function (): void {
    $context = new Context();

    $context->get('missing');
})->throws(VariableNotFoundException::class);

test('sets nested values with dot notation', function (): void {
    $context = new Context();
    $context->set('user.email', 'test@example.com');

    expect($context->get('user.email'))->toBe('test@example.com');
});

test('merges contexts correctly', function (): void {
    $context1 = Context::fromArray(['a' => 1]);
    $context2 = Context::fromArray(['b' => 2]);

    $merged = $context1->merge($context2);

    expect($merged->get('a'))->toBe(1)
        ->and($merged->get('b'))->toBe(2);
});

test('works with objects', function (): void {
    $obj = new stdClass();
    $obj->name = 'Test';

    $context = Context::fromArray(['user' => $obj]);

    expect($context->get('user.name'))->toBe('Test');
});

test('has returns false for missing variable', function (): void {
    $context = new Context();

    expect($context->has('missing'))->toBeFalse()
        ->and($context->has('user.missing'))->toBeFalse();
});

test('all returns all data', function (): void {
    $data = ['a' => 1, 'b' => 2];
    $context = Context::fromArray($data);

    expect($context->all())->toBe($data);
});
