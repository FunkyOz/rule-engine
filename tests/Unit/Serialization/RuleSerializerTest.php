<?php

declare(strict_types=1);

use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Operator\Comparison\EqualOperator;
use RuleEngine\Operator\Comparison\GreaterThanOrEqualOperator;
use RuleEngine\Operator\Logical\AndOperator;
use RuleEngine\Operator\Set\InOperator;
use RuleEngine\Rule\Rule;
use RuleEngine\Rule\RuleSet;
use RuleEngine\Serialization\RuleSerializer;

beforeEach(function (): void {
    $this->serializer = new RuleSerializer();
});

test('serializes simple rule', function (): void {
    $rule = new Rule('test', new LiteralExpression(42), ['key' => 'value']);

    $data = $this->serializer->serializeRule($rule);

    expect($data['name'])->toBe('test');
    expect($data)->toHaveKey('condition');
    expect($data['metadata'])->toBe(['key' => 'value']);
});

test('serializes rule without metadata', function (): void {
    $rule = new Rule('test', new LiteralExpression(42));

    $data = $this->serializer->serializeRule($rule);

    expect($data['metadata'])->toBe([]);
});

test('serializes literal expression', function (): void {
    $expression = new LiteralExpression(42);

    $data = $this->serializer->serializeExpression($expression);

    expect($data['type'])->toBe('literal');
    expect($data['value'])->toBe(42);
});

test('serializes literal expression with different types', function (): void {
    $cases = [
        'string' => 'hello',
        'int' => 123,
        'float' => 45.67,
        'bool' => true,
        'null' => null,
        'array' => [1, 2, 3],
    ];

    foreach ($cases as $type => $value) {
        $expression = new LiteralExpression($value);
        $data = $this->serializer->serializeExpression($expression);

        expect($data['type'])->toBe('literal', "Failed for type: $type");
        expect($data['value'])->toBe($value, "Failed for type: $type");
    }
});

test('serializes variable expression', function (): void {
    $expression = new VariableExpression('user.name');

    $data = $this->serializer->serializeExpression($expression);

    expect($data['type'])->toBe('variable');
    expect($data['name'])->toBe('user.name');
});

test('serializes operator expression', function (): void {
    $expression = new OperatorExpression(
        new EqualOperator(),
        [
            new VariableExpression('age'),
            new LiteralExpression(18),
        ]
    );

    $data = $this->serializer->serializeExpression($expression);

    expect($data['type'])->toBe('operator');
    expect($data['operator'])->toBe('=');
    expect($data['operands'])->toBeArray();
    expect($data['operands'])->toHaveCount(2);

    expect($data['operands'][0]['type'])->toBe('variable');
    expect($data['operands'][0]['name'])->toBe('age');

    expect($data['operands'][1]['type'])->toBe('literal');
    expect($data['operands'][1]['value'])->toBe(18);
});

test('serializes nested operator expression', function (): void {
    // (age >= 18) AND (verified = true)
    $ageCheck = new OperatorExpression(
        new GreaterThanOrEqualOperator(),
        [new VariableExpression('age'), new LiteralExpression(18)]
    );

    $verifiedCheck = new OperatorExpression(
        new EqualOperator(),
        [new VariableExpression('verified'), new LiteralExpression(true)]
    );

    $expression = new OperatorExpression(
        new AndOperator(),
        [$ageCheck, $verifiedCheck]
    );

    $data = $this->serializer->serializeExpression($expression);

    expect($data['type'])->toBe('operator');
    expect($data['operator'])->toBe('AND');
    expect($data['operands'])->toHaveCount(2);

    // Check first operand (age >= 18)
    expect($data['operands'][0]['type'])->toBe('operator');
    expect($data['operands'][0]['operator'])->toBe('>=');

    // Check second operand (verified = true)
    expect($data['operands'][1]['type'])->toBe('operator');
    expect($data['operands'][1]['operator'])->toBe('=');
});

test('serializes rule to json', function (): void {
    $rule = new Rule('test', new LiteralExpression(42), ['key' => 'value']);

    $json = $this->serializer->serializeRuleToJson($rule);

    expect($json)->json();

    $data = json_decode($json, true);
    expect($data['name'])->toBe('test');
    expect($data['metadata'])->toBe(['key' => 'value']);
});

test('serializes rule set', function (): void {
    $ruleSet = new RuleSet();
    $ruleSet->add(new Rule('rule1', new LiteralExpression(true)));
    $ruleSet->add(new Rule('rule2', new LiteralExpression(false)));

    $data = $this->serializer->serializeRuleSet($ruleSet);

    expect($data)->toHaveKey('rules');
    expect($data['rules'])->toHaveCount(2);

    expect($data['rules'][0]['name'])->toBe('rule1');
    expect($data['rules'][1]['name'])->toBe('rule2');
});

test('serializes empty rule set', function (): void {
    $ruleSet = new RuleSet();

    $data = $this->serializer->serializeRuleSet($ruleSet);

    expect($data)->toHaveKey('rules');
    expect($data['rules'])->toBe([]);
});

test('serializes rule set to json', function (): void {
    $ruleSet = new RuleSet();
    $ruleSet->add(new Rule('rule1', new LiteralExpression(true)));

    $json = $this->serializer->serializeRuleSetToJson($ruleSet);

    expect($json)->json();

    $data = json_decode($json, true);
    expect($data)->toHaveKey('rules');
    expect($data['rules'])->toHaveCount(1);
});

test('serializes complex rule', function (): void {
    // Create a complex rule with nested conditions and metadata
    $condition = new OperatorExpression(
        new AndOperator(),
        [
            new OperatorExpression(
                new GreaterThanOrEqualOperator(),
                [new VariableExpression('user.age'), new LiteralExpression(18)]
            ),
            new OperatorExpression(
                new InOperator(),
                [
                    new VariableExpression('user.role'),
                    new LiteralExpression(['admin', 'moderator']),
                ]
            ),
        ]
    );

    $rule = new Rule('complex_rule', $condition, [
        'priority' => 1,
        'category' => 'access',
        'description' => 'Complex access rule',
    ]);

    $data = $this->serializer->serializeRule($rule);

    expect($data['name'])->toBe('complex_rule');
    expect($data['metadata']['priority'])->toBe(1);
    expect($data['metadata']['category'])->toBe('access');

    expect($data['condition']['type'])->toBe('operator');
    expect($data['condition']['operator'])->toBe('AND');
});

test('json round trip', function (): void {
    $rule = new Rule(
        'test',
        new OperatorExpression(
            new EqualOperator(),
            [new VariableExpression('x'), new LiteralExpression(10)]
        ),
        ['key' => 'value']
    );

    $json = $this->serializer->serializeRuleToJson($rule);
    $decoded = json_decode($json, true);

    expect($decoded['name'])->toBe('test');
    expect($decoded['metadata'])->toBe(['key' => 'value']);
    expect($decoded['condition']['type'])->toBe('operator');
});
