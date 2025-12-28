<?php

declare(strict_types=1);

use InvalidArgumentException;
use RuleEngine\Operator\AbstractOperator;

function createConcreteOperator(string $name, int $arity): AbstractOperator
{
    return new class ($name, $arity) extends AbstractOperator {
        public function execute(array $operands): mixed
        {
            return null;
        }

        // Expose protected method for testing
        public function testValidate(array $operands): void
        {
            $this->validateOperandCount($operands);
        }
    };
}

test('getName returns operator name', function (): void {
    $operator = createConcreteOperator('TEST', 2);

    expect($operator->getName())->toBe('TEST');
});

test('getArity returns operator arity', function (): void {
    $operator = createConcreteOperator('TEST', 3);

    expect($operator->getArity())->toBe(3);
});

test('validateOperandCount accepts correct number of operands', function (): void {
    $operator = createConcreteOperator('TEST', 2);

    // Should not throw
    $operator->testValidate([1, 2]);

    expect(true)->toBeTrue();
});

test('validateOperandCount throws for too few operands', function (): void {
    $operator = createConcreteOperator('TEST', 2);

    $operator->testValidate([1]);
})->throws(InvalidArgumentException::class, 'Operator "TEST" requires 2 operand(s), 1 given');

test('validateOperandCount throws for too many operands', function (): void {
    $operator = createConcreteOperator('TEST', 2);

    $operator->testValidate([1, 2, 3]);
})->throws(InvalidArgumentException::class, 'Operator "TEST" requires 2 operand(s), 3 given');

test('validateOperandCount accepts any number for variadic operator', function (): void {
    $operator = createConcreteOperator('VARIADIC', -1);

    // Should accept any number >= 1
    $operator->testValidate([1]);
    $operator->testValidate([1, 2]);
    $operator->testValidate([1, 2, 3, 4, 5]);

    expect(true)->toBeTrue();
});

test('validateOperandCount throws for variadic operator with no operands', function (): void {
    $operator = createConcreteOperator('VARIADIC', -1);

    $operator->testValidate([]);
})->throws(InvalidArgumentException::class, 'Operator "VARIADIC" requires at least 1 operand, 0 given');

test('validateOperandCount with unary operator', function (): void {
    $operator = createConcreteOperator('NOT', 1);

    $operator->testValidate([true]);

    expect(true)->toBeTrue();
});

test('validateOperandCount throws for unary operator with multiple operands', function (): void {
    $operator = createConcreteOperator('NOT', 1);

    $operator->testValidate([true, false]);
})->throws(InvalidArgumentException::class, 'Operator "NOT" requires 1 operand(s), 2 given');
