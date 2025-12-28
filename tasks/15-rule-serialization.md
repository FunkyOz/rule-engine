---
title: Rule Serialization
status: done
priority: Medium
description: Implement serialization and deserialization of rules to/from arrays and JSON
---

## Objectives
- Serialize rules to array/JSON format
- Deserialize rules from array/JSON format
- Support storage and transmission of rules
- Enable rule configuration from external sources

## Deliverables
1. `src/Serialization/RuleSerializer.php`
2. `src/Serialization/RuleDeserializer.php`
3. `src/Exception/DeserializationException.php`

## Technical Details

### RuleSerializer

Serializes rules and expressions to array format.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Serialization;

use RuleEngine\Expression\ExpressionInterface;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Rule\Rule;
use RuleEngine\Rule\RuleSet;

final class RuleSerializer
{
    /**
     * Serialize a rule to an array.
     *
     * @return array<string, mixed>
     */
    public function serializeRule(Rule $rule): array
    {
        return [
            'name' => $rule->getName(),
            'condition' => $this->serializeExpression($rule->getCondition()),
            'metadata' => $rule->getMetadata(),
        ];
    }

    /**
     * Serialize a rule to JSON.
     */
    public function serializeRuleToJson(Rule $rule): string
    {
        return json_encode($this->serializeRule($rule), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    /**
     * Serialize a rule set to an array.
     *
     * @return array<string, mixed>
     */
    public function serializeRuleSet(RuleSet $ruleSet): array
    {
        $rules = [];

        foreach ($ruleSet->all() as $rule) {
            $rules[] = $this->serializeRule($rule);
        }

        return [
            'rules' => $rules,
        ];
    }

    /**
     * Serialize a rule set to JSON.
     */
    public function serializeRuleSetToJson(RuleSet $ruleSet): string
    {
        return json_encode($this->serializeRuleSet($ruleSet), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    /**
     * Serialize an expression to an array.
     *
     * @return array<string, mixed>
     */
    public function serializeExpression(ExpressionInterface $expression): array
    {
        return match (true) {
            $expression instanceof LiteralExpression => [
                'type' => 'literal',
                'value' => $expression->getValue(),
            ],
            $expression instanceof VariableExpression => [
                'type' => 'variable',
                'name' => $expression->getName(),
            ],
            $expression instanceof OperatorExpression => [
                'type' => 'operator',
                'operator' => $expression->getOperator()->getName(),
                'operands' => array_map(
                    fn(ExpressionInterface $op) => $this->serializeExpression($op),
                    $expression->getOperands()
                ),
            ],
            default => throw new \RuntimeException(
                'Unknown expression type: ' . get_class($expression)
            ),
        };
    }
}
```

### RuleDeserializer

Deserializes rules and expressions from array format.

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Serialization;

use RuleEngine\Exception\DeserializationException;
use RuleEngine\Expression\ExpressionInterface;
use RuleEngine\Expression\LiteralExpression;
use RuleEngine\Expression\OperatorExpression;
use RuleEngine\Expression\VariableExpression;
use RuleEngine\Registry\OperatorRegistryInterface;
use RuleEngine\Rule\Rule;
use RuleEngine\Rule\RuleSet;

final class RuleDeserializer
{
    public function __construct(
        private readonly OperatorRegistryInterface $registry
    ) {}

    /**
     * Deserialize a rule from an array.
     *
     * @param array<string, mixed> $data
     * @throws DeserializationException
     */
    public function deserializeRule(array $data): Rule
    {
        $this->validateRuleData($data);

        return new Rule(
            name: $data['name'],
            condition: $this->deserializeExpression($data['condition']),
            metadata: $data['metadata'] ?? []
        );
    }

    /**
     * Deserialize a rule from JSON.
     *
     * @throws DeserializationException
     */
    public function deserializeRuleFromJson(string $json): Rule
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new DeserializationException('Invalid JSON: ' . $e->getMessage());
        }

        return $this->deserializeRule($data);
    }

    /**
     * Deserialize a rule set from an array.
     *
     * @param array<string, mixed> $data
     * @throws DeserializationException
     */
    public function deserializeRuleSet(array $data): RuleSet
    {
        if (!isset($data['rules']) || !is_array($data['rules'])) {
            throw new DeserializationException('Invalid rule set data: missing "rules" array');
        }

        $ruleSet = new RuleSet();

        foreach ($data['rules'] as $ruleData) {
            $ruleSet->add($this->deserializeRule($ruleData));
        }

        return $ruleSet;
    }

    /**
     * Deserialize a rule set from JSON.
     *
     * @throws DeserializationException
     */
    public function deserializeRuleSetFromJson(string $json): RuleSet
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new DeserializationException('Invalid JSON: ' . $e->getMessage());
        }

        return $this->deserializeRuleSet($data);
    }

    /**
     * Deserialize an expression from an array.
     *
     * @param array<string, mixed> $data
     * @throws DeserializationException
     */
    public function deserializeExpression(array $data): ExpressionInterface
    {
        if (!isset($data['type'])) {
            throw new DeserializationException('Expression data missing "type" field');
        }

        return match ($data['type']) {
            'literal' => $this->deserializeLiteral($data),
            'variable' => $this->deserializeVariable($data),
            'operator' => $this->deserializeOperator($data),
            default => throw new DeserializationException(
                "Unknown expression type: {$data['type']}"
            ),
        };
    }

    /**
     * @param array<string, mixed> $data
     */
    private function deserializeLiteral(array $data): LiteralExpression
    {
        if (!array_key_exists('value', $data)) {
            throw new DeserializationException('Literal expression missing "value" field');
        }

        return new LiteralExpression($data['value']);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function deserializeVariable(array $data): VariableExpression
    {
        if (!isset($data['name'])) {
            throw new DeserializationException('Variable expression missing "name" field');
        }

        return new VariableExpression($data['name']);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function deserializeOperator(array $data): OperatorExpression
    {
        if (!isset($data['operator'])) {
            throw new DeserializationException('Operator expression missing "operator" field');
        }

        if (!isset($data['operands']) || !is_array($data['operands'])) {
            throw new DeserializationException('Operator expression missing "operands" array');
        }

        $operator = $this->registry->get($data['operator']);

        $operands = array_map(
            fn(array $opData) => $this->deserializeExpression($opData),
            $data['operands']
        );

        return new OperatorExpression($operator, $operands);
    }

    /**
     * @param array<string, mixed> $data
     * @throws DeserializationException
     */
    private function validateRuleData(array $data): void
    {
        if (!isset($data['name'])) {
            throw new DeserializationException('Rule data missing "name" field');
        }

        if (!isset($data['condition'])) {
            throw new DeserializationException('Rule data missing "condition" field');
        }

        if (!is_array($data['condition'])) {
            throw new DeserializationException('Rule "condition" must be an array');
        }
    }
}
```

### DeserializationException

```php
<?php

declare(strict_types=1);

namespace RuleEngine\Exception;

final class DeserializationException extends RuleEngineException
{
    public function __construct(string $message)
    {
        parent::__construct("Deserialization error: {$message}");
    }
}
```

## Serialized Format Example

```json
{
    "name": "adult_premium_user",
    "condition": {
        "type": "operator",
        "operator": "AND",
        "operands": [
            {
                "type": "operator",
                "operator": ">=",
                "operands": [
                    {
                        "type": "variable",
                        "name": "user.age"
                    },
                    {
                        "type": "literal",
                        "value": 18
                    }
                ]
            },
            {
                "type": "operator",
                "operator": "IN",
                "operands": [
                    {
                        "type": "variable",
                        "name": "user.subscription"
                    },
                    {
                        "type": "literal",
                        "value": ["premium", "enterprise"]
                    }
                ]
            }
        ]
    },
    "metadata": {
        "priority": 1,
        "category": "access"
    }
}
```

## Usage Example

```php
use RuleEngine\Registry\OperatorRegistry;
use RuleEngine\Serialization\RuleSerializer;
use RuleEngine\Serialization\RuleDeserializer;

$registry = OperatorRegistry::withDefaults();
$serializer = new RuleSerializer();
$deserializer = new RuleDeserializer($registry);

// Serialize a rule
$rule = /* create rule */;
$json = $serializer->serializeRuleToJson($rule);

// Store in database, file, or transmit...

// Deserialize the rule
$loadedRule = $deserializer->deserializeRuleFromJson($json);

// Use the loaded rule
$result = $loadedRule->evaluate($context);
```

## Dependencies
- Task 12 - Rule Definition

## Estimated Complexity
**Medium** - Recursive serialization/deserialization of expression trees

## Implementation Notes
- Serialization format is JSON-compatible
- All expression types have a consistent structure
- Deserialization requires the operator registry for lookup
- Consider adding schema validation in the future
- Support for custom expression types can be added via extension

## Acceptance Criteria
- [x] `RuleSerializer` converts rules to arrays and JSON
- [x] `RuleDeserializer` creates rules from arrays and JSON
- [x] All expression types serialize correctly
- [x] Nested expressions serialize/deserialize correctly
- [x] `DeserializationException` thrown for invalid data
- [x] Metadata is preserved through serialization
- [x] Round-trip serialization produces equivalent rules
- [x] PHPStan passes at level 8
- [x] Unit tests cover serialization and edge cases
