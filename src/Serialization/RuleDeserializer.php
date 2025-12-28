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
    ) {
    }

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
            fn (array $opData) => $this->deserializeExpression($opData),
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
