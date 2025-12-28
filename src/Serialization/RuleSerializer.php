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
                    fn (ExpressionInterface $op) => $this->serializeExpression($op),
                    $expression->getOperands()
                ),
            ],
            default => throw new \RuntimeException(
                'Unknown expression type: ' . get_class($expression)
            ),
        };
    }
}
