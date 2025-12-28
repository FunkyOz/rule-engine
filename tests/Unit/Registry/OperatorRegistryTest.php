<?php

declare(strict_types=1);

use RuleEngine\Exception\OperatorNotFoundException;
use RuleEngine\Operator\OperatorInterface;
use RuleEngine\Registry\OperatorRegistry;

function createMockOperatorForRegistry(string $name, int $arity): OperatorInterface
{
    return new class ($name, $arity) implements OperatorInterface {
        public function __construct(
            private string $name,
            private int $arity
        ) {
        }

        public function getName(): string
        {
            return $this->name;
        }

        public function getArity(): int
        {
            return $this->arity;
        }

        public function execute(array $operands): mixed
        {
            return null;
        }
    };
}

beforeEach(function (): void {
    $this->registry = new OperatorRegistry();
});

test('registers operator', function (): void {
    $operator = createMockOperatorForRegistry('TEST', 2);

    $this->registry->register($operator);

    expect($this->registry->has('TEST'))->toBeTrue();
});

test('gets registered operator', function (): void {
    $operator = createMockOperatorForRegistry('ADD', 2);

    $this->registry->register($operator);

    $retrieved = $this->registry->get('ADD');

    expect($retrieved)->toBe($operator);
});

test('throws exception for unregistered operator', function (): void {
    $this->registry->get('MISSING');
})->throws(OperatorNotFoundException::class, "Operator 'MISSING' not found in registry");

test('has returns true for registered operator', function (): void {
    $operator = createMockOperatorForRegistry('EQ', 2);

    $this->registry->register($operator);

    expect($this->registry->has('EQ'))->toBeTrue();
});

test('has returns false for unregistered operator', function (): void {
    expect($this->registry->has('NOTHERE'))->toBeFalse();
});

test('names returns all operator names', function (): void {
    $this->registry->register(createMockOperatorForRegistry('ADD', 2));
    $this->registry->register(createMockOperatorForRegistry('SUB', 2));
    $this->registry->register(createMockOperatorForRegistry('MUL', 2));

    $names = $this->registry->names();

    expect($names)->toBe(['ADD', 'SUB', 'MUL']);
});

test('names returns empty array when no operators', function (): void {
    $names = $this->registry->names();

    expect($names)->toBe([]);
});

test('register many registers multiple operators', function (): void {
    $operators = [
        createMockOperatorForRegistry('ADD', 2),
        createMockOperatorForRegistry('SUB', 2),
        createMockOperatorForRegistry('MUL', 2),
    ];

    $this->registry->registerMany($operators);

    expect($this->registry->has('ADD'))->toBeTrue();
    expect($this->registry->has('SUB'))->toBeTrue();
    expect($this->registry->has('MUL'))->toBeTrue();
});

test('all returns all operators', function (): void {
    $add = createMockOperatorForRegistry('ADD', 2);
    $sub = createMockOperatorForRegistry('SUB', 2);

    $this->registry->register($add);
    $this->registry->register($sub);

    $all = $this->registry->all();

    expect($all)->toBe(['ADD' => $add, 'SUB' => $sub]);
});

test('all returns empty array when no operators', function (): void {
    $all = $this->registry->all();

    expect($all)->toBe([]);
});

test('registering operator with same name overwrites previous', function (): void {
    $operator1 = createMockOperatorForRegistry('TEST', 2);
    $operator2 = createMockOperatorForRegistry('TEST', 3);

    $this->registry->register($operator1);
    $this->registry->register($operator2);

    $retrieved = $this->registry->get('TEST');

    expect($retrieved)->toBe($operator2);
    expect($retrieved->getArity())->toBe(3);
});

test('with defaults creates empty registry', function (): void {
    $registry = OperatorRegistry::withDefaults();

    expect($registry)->toBeInstanceOf(OperatorRegistry::class);
    expect($registry->names())->toBe([]);
});
