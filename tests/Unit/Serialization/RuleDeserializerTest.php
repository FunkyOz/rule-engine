<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\Exception\DeserializationException;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\Comparison\EqualOperator;
use RuleEngine\Operator\Comparison\GreaterThanOperator;
use RuleEngine\Operator\Comparison\GreaterThanOrEqualOperator;
use RuleEngine\Operator\Comparison\IdenticalOperator;
use RuleEngine\Operator\Comparison\LessThanOperator;
use RuleEngine\Operator\Comparison\LessThanOrEqualOperator;
use RuleEngine\Operator\Comparison\NotEqualOperator;
use RuleEngine\Operator\Comparison\NotIdenticalOperator;
use RuleEngine\Operator\Logical\AndOperator;
use RuleEngine\Operator\Logical\NotOperator;
use RuleEngine\Operator\Logical\OrOperator;
use RuleEngine\Operator\Logical\XorOperator;
use RuleEngine\Operator\Math\AddOperator;
use RuleEngine\Operator\Math\DivideOperator;
use RuleEngine\Operator\Math\ModuloOperator;
use RuleEngine\Operator\Math\MultiplyOperator;
use RuleEngine\Operator\Math\PowerOperator;
use RuleEngine\Operator\Math\SubtractOperator;
use RuleEngine\Operator\Set\ContainsOperator;
use RuleEngine\Operator\Set\DiffOperator;
use RuleEngine\Operator\Set\InOperator;
use RuleEngine\Operator\Set\IntersectOperator;
use RuleEngine\Operator\Set\NotInOperator;
use RuleEngine\Operator\Set\SubsetOperator;
use RuleEngine\Operator\Set\UnionOperator;
use RuleEngine\Operator\String\ContainsStringOperator;
use RuleEngine\Operator\String\EndsWithOperator;
use RuleEngine\Operator\String\MatchesOperator;
use RuleEngine\Operator\String\StartsWithOperator;
use RuleEngine\Registry\OperatorRegistry;
use RuleEngine\Serialization\RuleDeserializer;

function registerDefaultOperatorsForDeserializer(OperatorRegistry $registry): void
{
    // Comparison operators
    $registry->registerMany([
        new EqualOperator(),
        new NotEqualOperator(),
        new LessThanOperator(),
        new LessThanOrEqualOperator(),
        new GreaterThanOperator(),
        new GreaterThanOrEqualOperator(),
        new IdenticalOperator(),
        new NotIdenticalOperator(),
    ]);

    // Logical operators
    $registry->registerMany([
        new AndOperator(),
        new OrOperator(),
        new NotOperator(),
        new XorOperator(),
    ]);

    // Math operators
    $registry->registerMany([
        new AddOperator(),
        new SubtractOperator(),
        new MultiplyOperator(),
        new DivideOperator(),
        new ModuloOperator(),
        new PowerOperator(),
    ]);

    // Set operators
    $registry->registerMany([
        new InOperator(),
        new NotInOperator(),
        new ContainsOperator(),
        new SubsetOperator(),
        new UnionOperator(),
        new IntersectOperator(),
        new DiffOperator(),
    ]);

    // String operators
    $registry->registerMany([
        new StartsWithOperator(),
        new EndsWithOperator(),
        new ContainsStringOperator(),
        new MatchesOperator(),
    ]);
}

beforeEach(function (): void {
    $registry = new OperatorRegistry();
    registerDefaultOperatorsForDeserializer($registry);

    $this->deserializer = new RuleDeserializer($registry);
});

test('deserializes simple rule', function (): void {
    $data = [
        'name' => 'test',
        'condition' => [
            'type' => 'literal',
            'value' => true,
        ],
        'metadata' => ['key' => 'value'],
    ];

    $rule = $this->deserializer->deserializeRule($data);

    expect($rule->getName())->toBe('test');
    expect($rule->getMeta('key'))->toBe('value');
});

test('deserializes rule without metadata', function (): void {
    $data = [
        'name' => 'test',
        'condition' => [
            'type' => 'literal',
            'value' => true,
        ],
    ];

    $rule = $this->deserializer->deserializeRule($data);

    expect($rule->getName())->toBe('test');
    expect($rule->getMetadata())->toBe([]);
});

test('throws when rule data missing name', function (): void {
    $data = [
        'condition' => [
            'type' => 'literal',
            'value' => true,
        ],
    ];

    $this->deserializer->deserializeRule($data);
})->throws(DeserializationException::class, 'Rule data missing "name" field');

test('throws when rule data missing condition', function (): void {
    $data = [
        'name' => 'test',
    ];

    $this->deserializer->deserializeRule($data);
})->throws(DeserializationException::class, 'Rule data missing "condition" field');

test('throws when condition is not array', function (): void {
    $data = [
        'name' => 'test',
        'condition' => 'invalid',
    ];

    $this->deserializer->deserializeRule($data);
})->throws(DeserializationException::class, 'Rule "condition" must be an array');

test('deserializes literal expression', function (): void {
    $data = [
        'type' => 'literal',
        'value' => 42,
    ];

    $expression = $this->deserializer->deserializeExpression($data);

    expect($expression)->toBeInstanceOf(LiteralExpression::class);
    expect($expression->getValue())->toBe(42);
});

test('deserializes literal expression with different types', function (): void {
    $cases = [
        'string' => 'hello',
        'int' => 123,
        'float' => 45.67,
        'bool' => true,
        'null' => null,
        'array' => [1, 2, 3],
    ];

    foreach ($cases as $type => $value) {
        $data = [
            'type' => 'literal',
            'value' => $value,
        ];

        $expression = $this->deserializer->deserializeExpression($data);

        expect($expression)->toBeInstanceOf(LiteralExpression::class, "Failed for type: $type");
        expect($expression->getValue())->toBe($value, "Failed for type: $type");
    }
});

test('deserializes variable expression', function (): void {
    $data = [
        'type' => 'variable',
        'name' => 'user.name',
    ];

    $expression = $this->deserializer->deserializeExpression($data);

    expect($expression)->toBeInstanceOf(VariableExpression::class);
    expect($expression->getName())->toBe('user.name');
});

test('deserializes operator expression', function (): void {
    $data = [
        'type' => 'operator',
        'operator' => '=',
        'operands' => [
            ['type' => 'variable', 'name' => 'age'],
            ['type' => 'literal', 'value' => 18],
        ],
    ];

    $expression = $this->deserializer->deserializeExpression($data);

    expect($expression)->toBeInstanceOf(OperatorExpression::class);
    expect($expression->getOperator()->getName())->toBe('=');

    $operands = $expression->getOperands();
    expect($operands)->toHaveCount(2);
    expect($operands[0])->toBeInstanceOf(VariableExpression::class);
    expect($operands[1])->toBeInstanceOf(LiteralExpression::class);
});

test('deserializes nested operator expression', function (): void {
    // (age >= 18) AND (verified = true)
    $data = [
        'type' => 'operator',
        'operator' => 'AND',
        'operands' => [
            [
                'type' => 'operator',
                'operator' => '>=',
                'operands' => [
                    ['type' => 'variable', 'name' => 'age'],
                    ['type' => 'literal', 'value' => 18],
                ],
            ],
            [
                'type' => 'operator',
                'operator' => '=',
                'operands' => [
                    ['type' => 'variable', 'name' => 'verified'],
                    ['type' => 'literal', 'value' => true],
                ],
            ],
        ],
    ];

    $expression = $this->deserializer->deserializeExpression($data);

    expect($expression)->toBeInstanceOf(OperatorExpression::class);
    expect($expression->getOperator()->getName())->toBe('AND');

    $operands = $expression->getOperands();
    expect($operands)->toHaveCount(2);
    expect($operands[0])->toBeInstanceOf(OperatorExpression::class);
    expect($operands[1])->toBeInstanceOf(OperatorExpression::class);
});

test('throws when expression missing type', function (): void {
    $data = [
        'value' => 42,
    ];

    $this->deserializer->deserializeExpression($data);
})->throws(DeserializationException::class, 'Expression data missing "type" field');

test('throws when expression type is unknown', function (): void {
    $data = [
        'type' => 'unknown',
        'value' => 42,
    ];

    $this->deserializer->deserializeExpression($data);
})->throws(DeserializationException::class, 'Unknown expression type: unknown');

test('throws when literal missing value', function (): void {
    $data = [
        'type' => 'literal',
    ];

    $this->deserializer->deserializeExpression($data);
})->throws(DeserializationException::class, 'Literal expression missing "value" field');

test('throws when variable missing name', function (): void {
    $data = [
        'type' => 'variable',
    ];

    $this->deserializer->deserializeExpression($data);
})->throws(DeserializationException::class, 'Variable expression missing "name" field');

test('throws when operator missing operator field', function (): void {
    $data = [
        'type' => 'operator',
        'operands' => [],
    ];

    $this->deserializer->deserializeExpression($data);
})->throws(DeserializationException::class, 'Operator expression missing "operator" field');

test('throws when operator missing operands', function (): void {
    $data = [
        'type' => 'operator',
        'operator' => '=',
    ];

    $this->deserializer->deserializeExpression($data);
})->throws(DeserializationException::class, 'Operator expression missing "operands" array');

test('deserializes rule from json', function (): void {
    $json = json_encode([
        'name' => 'test',
        'condition' => [
            'type' => 'literal',
            'value' => true,
        ],
        'metadata' => ['key' => 'value'],
    ]);

    $rule = $this->deserializer->deserializeRuleFromJson($json);

    expect($rule->getName())->toBe('test');
    expect($rule->getMeta('key'))->toBe('value');
});

test('throws when json is invalid', function (): void {
    $json = 'invalid json {';

    $this->deserializer->deserializeRuleFromJson($json);
})->throws(DeserializationException::class, 'Invalid JSON');

test('deserializes rule set', function (): void {
    $data = [
        'rules' => [
            [
                'name' => 'rule1',
                'condition' => ['type' => 'literal', 'value' => true],
            ],
            [
                'name' => 'rule2',
                'condition' => ['type' => 'literal', 'value' => false],
            ],
        ],
    ];

    $ruleSet = $this->deserializer->deserializeRuleSet($data);

    expect($ruleSet->count())->toBe(2);
    expect($ruleSet->has('rule1'))->toBeTrue();
    expect($ruleSet->has('rule2'))->toBeTrue();
});

test('deserializes empty rule set', function (): void {
    $data = [
        'rules' => [],
    ];

    $ruleSet = $this->deserializer->deserializeRuleSet($data);

    expect($ruleSet->count())->toBe(0);
});

test('throws when rule set missing rules array', function (): void {
    $data = [];

    $this->deserializer->deserializeRuleSet($data);
})->throws(DeserializationException::class, 'Invalid rule set data: missing "rules" array');

test('deserializes rule set from json', function (): void {
    $json = json_encode([
        'rules' => [
            [
                'name' => 'rule1',
                'condition' => ['type' => 'literal', 'value' => true],
            ],
        ],
    ]);

    $ruleSet = $this->deserializer->deserializeRuleSetFromJson($json);

    expect($ruleSet->count())->toBe(1);
    expect($ruleSet->has('rule1'))->toBeTrue();
});

test('deserialized rule behaves correctly', function (): void {
    $data = [
        'name' => 'age_check',
        'condition' => [
            'type' => 'operator',
            'operator' => '>=',
            'operands' => [
                ['type' => 'variable', 'name' => 'age'],
                ['type' => 'literal', 'value' => 18],
            ],
        ],
    ];

    $rule = $this->deserializer->deserializeRule($data);

    $context1 = Context::fromArray(['age' => 25]);
    expect($rule->evaluate($context1))->toBeTrue();

    $context2 = Context::fromArray(['age' => 15]);
    expect($rule->evaluate($context2))->toBeFalse();
});

test('serialization round trip', function (): void {
    // Create a complex rule
    $originalData = [
        'name' => 'complex',
        'condition' => [
            'type' => 'operator',
            'operator' => 'AND',
            'operands' => [
                [
                    'type' => 'operator',
                    'operator' => '>=',
                    'operands' => [
                        ['type' => 'variable', 'name' => 'age'],
                        ['type' => 'literal', 'value' => 18],
                    ],
                ],
                [
                    'type' => 'operator',
                    'operator' => 'IN',
                    'operands' => [
                        ['type' => 'variable', 'name' => 'role'],
                        ['type' => 'literal', 'value' => ['admin', 'moderator']],
                    ],
                ],
            ],
        ],
        'metadata' => ['priority' => 1],
    ];

    $rule = $this->deserializer->deserializeRule($originalData);

    // Test that it works
    $context = Context::fromArray(['age' => 25, 'role' => 'admin']);
    expect($rule->evaluate($context))->toBeTrue();

    $context2 = Context::fromArray(['age' => 15, 'role' => 'admin']);
    expect($rule->evaluate($context2))->toBeFalse();
});
