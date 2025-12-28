<?php

declare(strict_types=1);

use RuleEngine\Exception\DivisionByZeroException;
use RuleEngine\Operator\Math\AddOperator;
use RuleEngine\Operator\Math\DivideOperator;
use RuleEngine\Operator\Math\ModuloOperator;
use RuleEngine\Operator\Math\MultiplyOperator;
use RuleEngine\Operator\Math\PowerOperator;
use RuleEngine\Operator\Math\SubtractOperator;

// AddOperator Tests
test('AddOperator name', function (): void {
    $operator = new AddOperator();
    expect($operator->getName())->toBe('+');
});

test('AddOperator is variadic', function (): void {
    $operator = new AddOperator();
    expect($operator->getArity())->toBe(-1);
});

test('AddOperator adds integers', function (): void {
    $operator = new AddOperator();
    expect($operator->execute([5, 10]))->toBe(15);
    expect($operator->execute([25, 25, 50]))->toBe(100);
});

test('AddOperator adds floats', function (): void {
    $operator = new AddOperator();
    expect($operator->execute([2.5, 5.0]))->toBe(7.5);
    expect($operator->execute([1.5, 3.0, 6.0]))->toBe(10.5);
});

test('AddOperator adds mixed types', function (): void {
    $operator = new AddOperator();
    expect($operator->execute([5, 7.5]))->toBe(12.5);
});

test('AddOperator works with negative numbers', function (): void {
    $operator = new AddOperator();
    expect($operator->execute([10, -10]))->toBe(0);
    expect($operator->execute([-2, -3]))->toBe(-5);
});

test('AddOperator throws on empty operands', function (): void {
    $operator = new AddOperator();
    $operator->execute([]);
})->throws(InvalidArgumentException::class);

// SubtractOperator Tests
test('SubtractOperator name', function (): void {
    $operator = new SubtractOperator();
    expect($operator->getName())->toBe('-');
});

test('SubtractOperator is binary', function (): void {
    $operator = new SubtractOperator();
    expect($operator->getArity())->toBe(2);
});

test('SubtractOperator subtracts integers', function (): void {
    $operator = new SubtractOperator();
    expect($operator->execute([10, 5]))->toBe(5);
    expect($operator->execute([5, 10]))->toBe(-5);
});

test('SubtractOperator subtracts floats', function (): void {
    $operator = new SubtractOperator();
    expect($operator->execute([7.5, 5.0]))->toBe(2.5);
});

test('SubtractOperator works with negative numbers', function (): void {
    $operator = new SubtractOperator();
    expect($operator->execute([10, -5]))->toBe(15);
    expect($operator->execute([-5, -3]))->toBe(-2);
});

test('SubtractOperator throws on invalid operand count', function (): void {
    $operator = new SubtractOperator();
    $operator->execute([5]);
})->throws(InvalidArgumentException::class);

// MultiplyOperator Tests
test('MultiplyOperator name', function (): void {
    $operator = new MultiplyOperator();
    expect($operator->getName())->toBe('*');
});

test('MultiplyOperator is variadic', function (): void {
    $operator = new MultiplyOperator();
    expect($operator->getArity())->toBe(-1);
});

test('MultiplyOperator multiplies integers', function (): void {
    $operator = new MultiplyOperator();
    expect($operator->execute([5, 10]))->toBe(50);
    expect($operator->execute([2, 3, 4, 5]))->toBe(120);
});

test('MultiplyOperator multiplies floats', function (): void {
    $operator = new MultiplyOperator();
    expect($operator->execute([2.5, 5.0]))->toBe(12.5);
});

test('MultiplyOperator works with negative numbers', function (): void {
    $operator = new MultiplyOperator();
    expect($operator->execute([10, -5]))->toBe(-50);
    expect($operator->execute([-5, -3]))->toBe(15);
});

test('MultiplyOperator throws on empty operands', function (): void {
    $operator = new MultiplyOperator();
    $operator->execute([]);
})->throws(InvalidArgumentException::class);

// DivideOperator Tests
test('DivideOperator name', function (): void {
    $operator = new DivideOperator();
    expect($operator->getName())->toBe('/');
});

test('DivideOperator is binary', function (): void {
    $operator = new DivideOperator();
    expect($operator->getArity())->toBe(2);
});

test('DivideOperator divides integers', function (): void {
    $operator = new DivideOperator();
    expect($operator->execute([10, 2]))->toBe(5);
    expect($operator->execute([5, 2]))->toBe(2.5);
});

test('DivideOperator divides floats', function (): void {
    $operator = new DivideOperator();
    expect($operator->execute([7.5, 3.0]))->toBe(2.5);
});

test('DivideOperator works with negative numbers', function (): void {
    $operator = new DivideOperator();
    expect($operator->execute([10, -5]))->toBe(-2);
    expect($operator->execute([-5, -2]))->toBe(2.5);
});

test('DivideOperator throws on division by zero', function (): void {
    $operator = new DivideOperator();
    $operator->execute([10, 0]);
})->throws(DivisionByZeroException::class, 'Division by zero');

test('DivideOperator throws on invalid operand count', function (): void {
    $operator = new DivideOperator();
    $operator->execute([5]);
})->throws(InvalidArgumentException::class);

// ModuloOperator Tests
test('ModuloOperator name', function (): void {
    $operator = new ModuloOperator();
    expect($operator->getName())->toBe('%');
});

test('ModuloOperator is binary', function (): void {
    $operator = new ModuloOperator();
    expect($operator->getArity())->toBe(2);
});

test('ModuloOperator calculates remainder', function (): void {
    $operator = new ModuloOperator();
    expect($operator->execute([10, 3]))->toBe(1);
    expect($operator->execute([10, 5]))->toBe(0);
    expect($operator->execute([17, 5]))->toBe(2);
});

test('ModuloOperator works with negative numbers', function (): void {
    $operator = new ModuloOperator();
    expect($operator->execute([-10, 3]))->toBe(-1);
    expect($operator->execute([10, -3]))->toBe(1);
});

test('ModuloOperator casts to int', function (): void {
    $operator = new ModuloOperator();
    // Floats are cast to int before modulo
    expect($operator->execute([10.7, 3.2]))->toBe(1);
});

test('ModuloOperator throws on modulo by zero', function (): void {
    $operator = new ModuloOperator();
    $operator->execute([10, 0]);
})->throws(DivisionByZeroException::class, 'Division by zero');

test('ModuloOperator throws on invalid operand count', function (): void {
    $operator = new ModuloOperator();
    $operator->execute([5]);
})->throws(InvalidArgumentException::class);

// PowerOperator Tests
test('PowerOperator name', function (): void {
    $operator = new PowerOperator();
    expect($operator->getName())->toBe('^');
});

test('PowerOperator is binary', function (): void {
    $operator = new PowerOperator();
    expect($operator->getArity())->toBe(2);
});

test('PowerOperator calculates power', function (): void {
    $operator = new PowerOperator();
    expect($operator->execute([2, 3]))->toBe(8);
    expect($operator->execute([10, 2]))->toBe(100);
    expect($operator->execute([5, 0]))->toBe(1);
});

test('PowerOperator works with negative exponents', function (): void {
    $operator = new PowerOperator();
    expect($operator->execute([2, -2]))->toBe(0.25);
    expect($operator->execute([10, -1]))->toBe(0.1);
});

test('PowerOperator works with fractional exponents', function (): void {
    $operator = new PowerOperator();
    expect($operator->execute([9, 0.5]))->toBe(3.0); // Square root
    expect($operator->execute([8, 1 / 3]))->toBe(2.0); // Cube root
});

test('PowerOperator works with negative base', function (): void {
    $operator = new PowerOperator();
    expect($operator->execute([-2, 3]))->toBe(-8);
    expect($operator->execute([-2, 2]))->toBe(4);
});

test('PowerOperator throws on invalid operand count', function (): void {
    $operator = new PowerOperator();
    $operator->execute([5]);
})->throws(InvalidArgumentException::class);

// Edge Cases
test('math operators handle zero', function (): void {
    $add = new AddOperator();
    $subtract = new SubtractOperator();
    $multiply = new MultiplyOperator();

    expect($add->execute([5, 0]))->toBe(5);
    expect($subtract->execute([5, 0]))->toBe(5);
    expect($multiply->execute([5, 0]))->toBe(0);
});

test('math operators preserve types', function (): void {
    $add = new AddOperator();
    $multiply = new MultiplyOperator();

    // Integer operations return integers
    expect($add->execute([5, 10]))->toBeInt();
    expect($multiply->execute([2, 3]))->toBeInt();

    // Float operations return floats
    expect($add->execute([5.0, 10.0]))->toBeFloat();
    expect($multiply->execute([2.5, 3.0]))->toBeFloat();
});
