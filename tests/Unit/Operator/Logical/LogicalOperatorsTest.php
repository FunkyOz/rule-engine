<?php

declare(strict_types=1);

use RuleEngine\Operator\Logical\AndOperator;
use RuleEngine\Operator\Logical\NotOperator;
use RuleEngine\Operator\Logical\OrOperator;
use RuleEngine\Operator\Logical\XorOperator;

// AndOperator Tests
test('AndOperator name', function (): void {
    $operator = new AndOperator();
    expect($operator->getName())->toBe('AND');
});

test('AndOperator is variadic', function (): void {
    $operator = new AndOperator();
    expect($operator->getArity())->toBe(-1);
});

test('AndOperator returns true when all operands are truthy', function (): void {
    $operator = new AndOperator();
    expect($operator->execute([true, true]))->toBeTrue();
    expect($operator->execute([true, 1, 'hello']))->toBeTrue();
    expect($operator->execute([1, 2, 3]))->toBeTrue();
});

test('AndOperator returns false when any operand is falsy', function (): void {
    $operator = new AndOperator();
    expect($operator->execute([true, false]))->toBeFalse();
    expect($operator->execute([false, true]))->toBeFalse();
    expect($operator->execute([true, 0]))->toBeFalse();
    expect($operator->execute([true, '']))->toBeFalse();
    expect($operator->execute([true, null]))->toBeFalse();
});

test('AndOperator works with multiple operands', function (): void {
    $operator = new AndOperator();
    expect($operator->execute([true, true, true, true]))->toBeTrue();
    expect($operator->execute([true, true, false, true]))->toBeFalse();
});

test('AndOperator short circuits', function (): void {
    $operator = new AndOperator();
    // If it short-circuits, it should stop at the first false
    expect($operator->execute([false, true, true]))->toBeFalse();
});

test('AndOperator throws on empty operands', function (): void {
    $operator = new AndOperator();
    $operator->execute([]);
})->throws(InvalidArgumentException::class);

// OrOperator Tests
test('OrOperator name', function (): void {
    $operator = new OrOperator();
    expect($operator->getName())->toBe('OR');
});

test('OrOperator is variadic', function (): void {
    $operator = new OrOperator();
    expect($operator->getArity())->toBe(-1);
});

test('OrOperator returns true when any operand is truthy', function (): void {
    $operator = new OrOperator();
    expect($operator->execute([true, false]))->toBeTrue();
    expect($operator->execute([false, true]))->toBeTrue();
    expect($operator->execute([false, false, true]))->toBeTrue();
    expect($operator->execute([0, '', 'hello']))->toBeTrue();
});

test('OrOperator returns false when all operands are falsy', function (): void {
    $operator = new OrOperator();
    expect($operator->execute([false, false]))->toBeFalse();
    expect($operator->execute([0, '', null, false]))->toBeFalse();
});

test('OrOperator works with multiple operands', function (): void {
    $operator = new OrOperator();
    expect($operator->execute([false, false, false, true]))->toBeTrue();
    expect($operator->execute([false, false, false, false]))->toBeFalse();
});

test('OrOperator short circuits', function (): void {
    $operator = new OrOperator();
    // If it short-circuits, it should stop at the first true
    expect($operator->execute([true, false, false]))->toBeTrue();
});

test('OrOperator throws on empty operands', function (): void {
    $operator = new OrOperator();
    $operator->execute([]);
})->throws(InvalidArgumentException::class);

// NotOperator Tests
test('NotOperator name', function (): void {
    $operator = new NotOperator();
    expect($operator->getName())->toBe('NOT');
});

test('NotOperator is unary', function (): void {
    $operator = new NotOperator();
    expect($operator->getArity())->toBe(1);
});

test('NotOperator negates true to false', function (): void {
    $operator = new NotOperator();
    expect($operator->execute([true]))->toBeFalse();
    expect($operator->execute([1]))->toBeFalse();
    expect($operator->execute(['hello']))->toBeFalse();
});

test('NotOperator negates false to true', function (): void {
    $operator = new NotOperator();
    expect($operator->execute([false]))->toBeTrue();
    expect($operator->execute([0]))->toBeTrue();
    expect($operator->execute(['']))->toBeTrue();
    expect($operator->execute([null]))->toBeTrue();
});

test('NotOperator throws on invalid operand count', function (): void {
    $operator = new NotOperator();
    $operator->execute([true, false]);
})->throws(InvalidArgumentException::class);

// XorOperator Tests
test('XorOperator name', function (): void {
    $operator = new XorOperator();
    expect($operator->getName())->toBe('XOR');
});

test('XorOperator is binary', function (): void {
    $operator = new XorOperator();
    expect($operator->getArity())->toBe(2);
});

test('XorOperator returns true when exactly one operand is truthy', function (): void {
    $operator = new XorOperator();
    expect($operator->execute([true, false]))->toBeTrue();
    expect($operator->execute([false, true]))->toBeTrue();
    expect($operator->execute([1, 0]))->toBeTrue();
    expect($operator->execute([0, 'hello']))->toBeTrue();
});

test('XorOperator returns false when both operands are truthy', function (): void {
    $operator = new XorOperator();
    expect($operator->execute([true, true]))->toBeFalse();
    expect($operator->execute([1, 'hello']))->toBeFalse();
});

test('XorOperator returns false when both operands are falsy', function (): void {
    $operator = new XorOperator();
    expect($operator->execute([false, false]))->toBeFalse();
    expect($operator->execute([0, '']))->toBeFalse();
    expect($operator->execute([null, false]))->toBeFalse();
});

test('XorOperator throws on invalid operand count', function (): void {
    $operator = new XorOperator();
    $operator->execute([true]);
})->throws(InvalidArgumentException::class);

// Edge Cases
test('logical operators handle mixed types', function (): void {
    $and = new AndOperator();
    $or = new OrOperator();
    $not = new NotOperator();
    $xor = new XorOperator();

    // AND with mixed types
    expect($and->execute([1, 'string', 2.5, [1]]))->toBeTrue();
    expect($and->execute([1, 'string', 0]))->toBeFalse();

    // OR with mixed types
    expect($or->execute([0, '', 'string']))->toBeTrue();
    expect($or->execute([0, '', null, false]))->toBeFalse();

    // NOT with mixed types
    expect($not->execute([[1, 2, 3]]))->toBeFalse();
    expect($not->execute([[]]))->toBeTrue();

    // XOR with mixed types
    expect($xor->execute(['string', 0]))->toBeTrue();
    expect($xor->execute(['hello', 'world']))->toBeFalse();
});
