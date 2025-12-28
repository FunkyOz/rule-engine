<?php

declare(strict_types=1);

use RuleEngine\Operator\Comparison\EqualOperator;
use RuleEngine\Operator\Comparison\GreaterThanOperator;
use RuleEngine\Operator\Comparison\GreaterThanOrEqualOperator;
use RuleEngine\Operator\Comparison\LessThanOperator;
use RuleEngine\Operator\Comparison\LessThanOrEqualOperator;
use RuleEngine\Operator\Comparison\NotEqualOperator;
use RuleEngine\Operator\Comparison\StrictEqualOperator;
use RuleEngine\Operator\Comparison\StrictNotEqualOperator;

// EqualOperator Tests
test('EqualOperator name', function (): void {
    $operator = new EqualOperator();
    expect($operator->getName())->toBe('=');
});

test('EqualOperator arity', function (): void {
    $operator = new EqualOperator();
    expect($operator->getArity())->toBe(2);
});

test('EqualOperator returns true for equal values', function (): void {
    $operator = new EqualOperator();
    expect($operator->execute([5, 5]))->toBeTrue();
    expect($operator->execute(['hello', 'hello']))->toBeTrue();
    expect($operator->execute([true, true]))->toBeTrue();
});

test('EqualOperator returns false for unequal values', function (): void {
    $operator = new EqualOperator();
    expect($operator->execute([5, 6]))->toBeFalse();
    expect($operator->execute(['hello', 'world']))->toBeFalse();
});

test('EqualOperator uses loose comparison', function (): void {
    $operator = new EqualOperator();
    expect($operator->execute([5, '5']))->toBeTrue();
    expect($operator->execute([0, false]))->toBeTrue();
    expect($operator->execute([1, true]))->toBeTrue();
});

test('EqualOperator handles null comparisons', function (): void {
    $operator = new EqualOperator();
    expect($operator->execute([null, null]))->toBeTrue();
    expect($operator->execute([null, 1]))->toBeFalse();
    // Note: null == '' is true in PHP (loose comparison)
    expect($operator->execute([null, '']))->toBeTrue();
});

test('EqualOperator throws on invalid operand count', function (): void {
    $operator = new EqualOperator();
    $operator->execute([5]);
})->throws(InvalidArgumentException::class);

// NotEqualOperator Tests
test('NotEqualOperator name', function (): void {
    $operator = new NotEqualOperator();
    expect($operator->getName())->toBe('!=');
});

test('NotEqualOperator arity', function (): void {
    $operator = new NotEqualOperator();
    expect($operator->getArity())->toBe(2);
});

test('NotEqualOperator returns true for unequal values', function (): void {
    $operator = new NotEqualOperator();
    expect($operator->execute([5, 6]))->toBeTrue();
    expect($operator->execute(['hello', 'world']))->toBeTrue();
});

test('NotEqualOperator returns false for equal values', function (): void {
    $operator = new NotEqualOperator();
    expect($operator->execute([5, 5]))->toBeFalse();
    expect($operator->execute(['hello', 'hello']))->toBeFalse();
});

test('NotEqualOperator uses loose comparison', function (): void {
    $operator = new NotEqualOperator();
    expect($operator->execute([5, '5']))->toBeFalse();
    expect($operator->execute([0, false]))->toBeFalse();
});

test('NotEqualOperator throws on invalid operand count', function (): void {
    $operator = new NotEqualOperator();
    $operator->execute([5]);
})->throws(InvalidArgumentException::class);

// LessThanOperator Tests
test('LessThanOperator name', function (): void {
    $operator = new LessThanOperator();
    expect($operator->getName())->toBe('<');
});

test('LessThanOperator arity', function (): void {
    $operator = new LessThanOperator();
    expect($operator->getArity())->toBe(2);
});

test('LessThanOperator returns true when first is less than second', function (): void {
    $operator = new LessThanOperator();
    expect($operator->execute([5, 10]))->toBeTrue();
    expect($operator->execute([-5, 0]))->toBeTrue();
    expect($operator->execute([1.5, 2.5]))->toBeTrue();
});

test('LessThanOperator returns false when first is not less than second', function (): void {
    $operator = new LessThanOperator();
    expect($operator->execute([10, 5]))->toBeFalse();
    expect($operator->execute([5, 5]))->toBeFalse();
});

test('LessThanOperator compares strings alphabetically', function (): void {
    $operator = new LessThanOperator();
    expect($operator->execute(['a', 'b']))->toBeTrue();
    expect($operator->execute(['b', 'a']))->toBeFalse();
});

test('LessThanOperator throws on invalid operand count', function (): void {
    $operator = new LessThanOperator();
    $operator->execute([5]);
})->throws(InvalidArgumentException::class);

// LessThanOrEqualOperator Tests
test('LessThanOrEqualOperator name', function (): void {
    $operator = new LessThanOrEqualOperator();
    expect($operator->getName())->toBe('<=');
});

test('LessThanOrEqualOperator arity', function (): void {
    $operator = new LessThanOrEqualOperator();
    expect($operator->getArity())->toBe(2);
});

test('LessThanOrEqualOperator returns true when first is less than or equal to second', function (): void {
    $operator = new LessThanOrEqualOperator();
    expect($operator->execute([5, 10]))->toBeTrue();
    expect($operator->execute([5, 5]))->toBeTrue();
    expect($operator->execute([1.5, 2.5]))->toBeTrue();
});

test('LessThanOrEqualOperator returns false when first is greater than second', function (): void {
    $operator = new LessThanOrEqualOperator();
    expect($operator->execute([10, 5]))->toBeFalse();
});

test('LessThanOrEqualOperator throws on invalid operand count', function (): void {
    $operator = new LessThanOrEqualOperator();
    $operator->execute([5]);
})->throws(InvalidArgumentException::class);

// GreaterThanOperator Tests
test('GreaterThanOperator name', function (): void {
    $operator = new GreaterThanOperator();
    expect($operator->getName())->toBe('>');
});

test('GreaterThanOperator arity', function (): void {
    $operator = new GreaterThanOperator();
    expect($operator->getArity())->toBe(2);
});

test('GreaterThanOperator returns true when first is greater than second', function (): void {
    $operator = new GreaterThanOperator();
    expect($operator->execute([10, 5]))->toBeTrue();
    expect($operator->execute([0, -5]))->toBeTrue();
    expect($operator->execute([2.5, 1.5]))->toBeTrue();
});

test('GreaterThanOperator returns false when first is not greater than second', function (): void {
    $operator = new GreaterThanOperator();
    expect($operator->execute([5, 10]))->toBeFalse();
    expect($operator->execute([5, 5]))->toBeFalse();
});

test('GreaterThanOperator compares strings alphabetically', function (): void {
    $operator = new GreaterThanOperator();
    expect($operator->execute(['b', 'a']))->toBeTrue();
    expect($operator->execute(['a', 'b']))->toBeFalse();
});

test('GreaterThanOperator throws on invalid operand count', function (): void {
    $operator = new GreaterThanOperator();
    $operator->execute([5]);
})->throws(InvalidArgumentException::class);

// GreaterThanOrEqualOperator Tests
test('GreaterThanOrEqualOperator name', function (): void {
    $operator = new GreaterThanOrEqualOperator();
    expect($operator->getName())->toBe('>=');
});

test('GreaterThanOrEqualOperator arity', function (): void {
    $operator = new GreaterThanOrEqualOperator();
    expect($operator->getArity())->toBe(2);
});

test('GreaterThanOrEqualOperator returns true when first is greater than or equal to second', function (): void {
    $operator = new GreaterThanOrEqualOperator();
    expect($operator->execute([10, 5]))->toBeTrue();
    expect($operator->execute([5, 5]))->toBeTrue();
    expect($operator->execute([2.5, 1.5]))->toBeTrue();
});

test('GreaterThanOrEqualOperator returns false when first is less than second', function (): void {
    $operator = new GreaterThanOrEqualOperator();
    expect($operator->execute([5, 10]))->toBeFalse();
});

test('GreaterThanOrEqualOperator throws on invalid operand count', function (): void {
    $operator = new GreaterThanOrEqualOperator();
    $operator->execute([5]);
})->throws(InvalidArgumentException::class);

// StrictEqualOperator Tests
test('StrictEqualOperator name', function (): void {
    $operator = new StrictEqualOperator();
    expect($operator->getName())->toBe('===');
});

test('StrictEqualOperator arity', function (): void {
    $operator = new StrictEqualOperator();
    expect($operator->getArity())->toBe(2);
});

test('StrictEqualOperator returns true for strictly equal values', function (): void {
    $operator = new StrictEqualOperator();
    expect($operator->execute([5, 5]))->toBeTrue();
    expect($operator->execute(['hello', 'hello']))->toBeTrue();
    expect($operator->execute([true, true]))->toBeTrue();
});

test('StrictEqualOperator returns false for unequal values', function (): void {
    $operator = new StrictEqualOperator();
    expect($operator->execute([5, 6]))->toBeFalse();
});

test('StrictEqualOperator uses strict comparison', function (): void {
    $operator = new StrictEqualOperator();
    expect($operator->execute([5, '5']))->toBeFalse();
    expect($operator->execute([0, false]))->toBeFalse();
    expect($operator->execute([1, true]))->toBeFalse();
});

test('StrictEqualOperator handles null comparisons strictly', function (): void {
    $operator = new StrictEqualOperator();
    expect($operator->execute([null, null]))->toBeTrue();
    expect($operator->execute([null, 0]))->toBeFalse();
    expect($operator->execute([null, '']))->toBeFalse();
});

test('StrictEqualOperator throws on invalid operand count', function (): void {
    $operator = new StrictEqualOperator();
    $operator->execute([5]);
})->throws(InvalidArgumentException::class);

// StrictNotEqualOperator Tests
test('StrictNotEqualOperator name', function (): void {
    $operator = new StrictNotEqualOperator();
    expect($operator->getName())->toBe('!==');
});

test('StrictNotEqualOperator arity', function (): void {
    $operator = new StrictNotEqualOperator();
    expect($operator->getArity())->toBe(2);
});

test('StrictNotEqualOperator returns true for strictly unequal values', function (): void {
    $operator = new StrictNotEqualOperator();
    expect($operator->execute([5, 6]))->toBeTrue();
    expect($operator->execute(['hello', 'world']))->toBeTrue();
});

test('StrictNotEqualOperator returns false for strictly equal values', function (): void {
    $operator = new StrictNotEqualOperator();
    expect($operator->execute([5, 5]))->toBeFalse();
    expect($operator->execute(['hello', 'hello']))->toBeFalse();
});

test('StrictNotEqualOperator uses strict comparison', function (): void {
    $operator = new StrictNotEqualOperator();
    expect($operator->execute([5, '5']))->toBeTrue();
    expect($operator->execute([0, false]))->toBeTrue();
    expect($operator->execute([1, true]))->toBeTrue();
});

test('StrictNotEqualOperator throws on invalid operand count', function (): void {
    $operator = new StrictNotEqualOperator();
    $operator->execute([5]);
})->throws(InvalidArgumentException::class);
