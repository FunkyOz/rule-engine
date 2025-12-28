<?php

declare(strict_types=1);

use RuleEngine\Context\Context;
use RuleEngine\RuleEngine;
use RuleEngine\Serialization\RuleDeserializer;
use RuleEngine\Serialization\RuleSerializer;

beforeEach(function (): void {
    $this->engine = RuleEngine::create();
    $this->serializer = new RuleSerializer();
    $this->deserializer = new RuleDeserializer($this->engine->getRegistry());
});

test('preserves rule behavior through serialization', function (): void {
    $originalRule = $this->engine->builder()
        ->name('complex_rule')
        ->when('user.age')->greaterThanOrEqual(18)
        ->andWhen('user.subscription')->in(['premium', 'enterprise'])
        ->then()
        ->meta('priority', 1)
        ->build();

    $json = $this->serializer->serializeRuleToJson($originalRule);
    $loadedRule = $this->deserializer->deserializeRuleFromJson($json);

    $context = Context::fromArray([
        'user' => [
            'age' => 25,
            'subscription' => 'premium',
        ],
    ]);

    // Both rules should evaluate the same
    expect($loadedRule->evaluate($context))->toBe($originalRule->evaluate($context));

    // Metadata should be preserved
    expect($loadedRule->getName())->toBe('complex_rule');
    expect($loadedRule->getMeta('priority'))->toBe(1);
});

test('serializes and deserializes simple rule', function (): void {
    $rule = $this->engine->builder()
        ->name('age_check')
        ->when('age')->greaterThanOrEqual(18)
        ->then()
        ->build();

    $json = $this->serializer->serializeRuleToJson($rule);
    $deserialized = $this->deserializer->deserializeRuleFromJson($json);

    // Test with passing context
    $context1 = Context::fromArray(['age' => 25]);
    expect($rule->evaluate($context1))->toBeTrue();
    expect($deserialized->evaluate($context1))->toBeTrue();

    // Test with failing context
    $context2 = Context::fromArray(['age' => 15]);
    expect($rule->evaluate($context2))->toBeFalse();
    expect($deserialized->evaluate($context2))->toBeFalse();
});

test('serializes rule with complex conditions', function (): void {
    $rule = $this->engine->builder()
        ->name('access_rule')
        ->when('user.role')->in(['admin', 'moderator'])
        ->andWhen('user.verified')->equals(true)
        ->andWhen('user.age')->greaterThanOrEqual(18)
        ->then()
        ->meta('category', 'access_control')
        ->meta('priority', 5)
        ->build();

    $json = $this->serializer->serializeRuleToJson($rule);
    $deserialized = $this->deserializer->deserializeRuleFromJson($json);

    $context = Context::fromArray([
        'user' => [
            'role' => 'admin',
            'verified' => true,
            'age' => 30,
        ],
    ]);

    expect($rule->evaluate($context))->toBeTrue();
    expect($deserialized->evaluate($context))->toBeTrue();

    // Verify metadata
    expect($deserialized->getMeta('category'))->toBe('access_control');
    expect($deserialized->getMeta('priority'))->toBe(5);
});

test('serializes rule set and preserves behavior', function (): void {
    $this->engine->addRules([
        $this->engine->builder()
            ->name('rule1')
            ->when('value')->greaterThan(10)
            ->then()
            ->build(),
        $this->engine->builder()
            ->name('rule2')
            ->when('status')->equals('active')
            ->then()
            ->build(),
    ]);

    // Get the rule set
    $ruleSet = new \RuleEngine\Rule\RuleSet();
    foreach ($this->engine->getRules() as $rule) {
        $ruleSet->add($rule);
    }

    // Serialize and deserialize
    $json = $this->serializer->serializeRuleSetToJson($ruleSet);
    $deserializedSet = $this->deserializer->deserializeRuleSetFromJson($json);

    expect($deserializedSet->count())->toBe(2);
    expect($deserializedSet->has('rule1'))->toBeTrue();
    expect($deserializedSet->has('rule2'))->toBeTrue();

    // Test evaluation
    $context = Context::fromArray(['value' => 15, 'status' => 'active']);

    expect($ruleSet->evaluateAll($context))->toBeTrue();
    expect($deserializedSet->evaluateAll($context))->toBeTrue();
});

test('serializes string operators', function (): void {
    $rule = $this->engine->builder()
        ->name('email_validation')
        ->when('email')->endsWith('@company.com')
        ->then()
        ->build();

    $json = $this->serializer->serializeRuleToJson($rule);
    $deserialized = $this->deserializer->deserializeRuleFromJson($json);

    $validContext = Context::fromArray(['email' => 'john@company.com']);
    $invalidContext = Context::fromArray(['email' => 'john@gmail.com']);

    expect($rule->evaluate($validContext))->toBeTrue();
    expect($deserialized->evaluate($validContext))->toBeTrue();

    expect($rule->evaluate($invalidContext))->toBeFalse();
    expect($deserialized->evaluate($invalidContext))->toBeFalse();
});

test('serializes regex patterns', function (): void {
    $rule = $this->engine->builder()
        ->name('phone_validation')
        ->when('phone')->matches('/^\d{3}-\d{3}-\d{4}$/')
        ->then()
        ->build();

    $json = $this->serializer->serializeRuleToJson($rule);
    $deserialized = $this->deserializer->deserializeRuleFromJson($json);

    $validContext = Context::fromArray(['phone' => '555-123-4567']);
    $invalidContext = Context::fromArray(['phone' => '5551234567']);

    expect($rule->evaluate($validContext))->toBeTrue();
    expect($deserialized->evaluate($validContext))->toBeTrue();

    expect($rule->evaluate($invalidContext))->toBeFalse();
    expect($deserialized->evaluate($invalidContext))->toBeFalse();
});

test('serializes set operators', function (): void {
    $rule = $this->engine->builder()
        ->name('role_check')
        ->when('role')->in(['admin', 'moderator', 'editor'])
        ->then()
        ->build();

    $json = $this->serializer->serializeRuleToJson($rule);
    $deserialized = $this->deserializer->deserializeRuleFromJson($json);

    $adminContext = Context::fromArray(['role' => 'admin']);
    $userContext = Context::fromArray(['role' => 'user']);

    expect($rule->evaluate($adminContext))->toBeTrue();
    expect($deserialized->evaluate($adminContext))->toBeTrue();

    expect($rule->evaluate($userContext))->toBeFalse();
    expect($deserialized->evaluate($userContext))->toBeFalse();
});

test('multiple serialization round trips', function (): void {
    $rule = $this->engine->builder()
        ->name('multi_round_trip')
        ->when('value')->greaterThanOrEqual(100)
        ->andWhen('status')->equals('approved')
        ->then()
        ->meta('version', 1)
        ->build();

    // First round trip
    $json1 = $this->serializer->serializeRuleToJson($rule);
    $deserialized1 = $this->deserializer->deserializeRuleFromJson($json1);

    // Second round trip
    $json2 = $this->serializer->serializeRuleToJson($deserialized1);
    $deserialized2 = $this->deserializer->deserializeRuleFromJson($json2);

    // Third round trip
    $json3 = $this->serializer->serializeRuleToJson($deserialized2);
    $deserialized3 = $this->deserializer->deserializeRuleFromJson($json3);

    $context = Context::fromArray(['value' => 150, 'status' => 'approved']);

    // All should evaluate the same
    expect($rule->evaluate($context))->toBeTrue();
    expect($deserialized1->evaluate($context))->toBeTrue();
    expect($deserialized2->evaluate($context))->toBeTrue();
    expect($deserialized3->evaluate($context))->toBeTrue();

    // Metadata should be preserved
    expect($deserialized3->getMeta('version'))->toBe(1);
});

test('serializes nested context access', function (): void {
    $rule = $this->engine->builder()
        ->name('nested_check')
        ->when('user.profile.age')->greaterThanOrEqual(21)
        ->andWhen('user.profile.country')->equals('USA')
        ->then()
        ->build();

    $json = $this->serializer->serializeRuleToJson($rule);
    $deserialized = $this->deserializer->deserializeRuleFromJson($json);

    $context = Context::fromArray([
        'user' => [
            'profile' => [
                'age' => 25,
                'country' => 'USA',
            ],
        ],
    ]);

    expect($rule->evaluate($context))->toBeTrue();
    expect($deserialized->evaluate($context))->toBeTrue();
});

test('json structure is readable', function (): void {
    $rule = $this->engine->builder()
        ->name('test_rule')
        ->when('age')->greaterThanOrEqual(18)
        ->then()
        ->meta('description', 'Age verification')
        ->build();

    $json = $this->serializer->serializeRuleToJson($rule);

    // Verify JSON is properly formatted
    expect($json)->toBeJson();

    // Decode and verify structure
    $data = json_decode($json, true);

    expect($data)->toHaveKey('name');
    expect($data)->toHaveKey('condition');
    expect($data)->toHaveKey('metadata');

    expect($data['name'])->toBe('test_rule');
    expect($data['metadata']['description'])->toBe('Age verification');

    // Verify condition structure
    expect($data['condition']['type'])->toBe('operator');
    expect($data['condition']['operator'])->toBe('>=');
    expect($data['condition']['operands'])->toBeArray();
});
