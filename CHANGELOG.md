# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-12-30

### Added

- **String Operators Enhancement**
  - `ConcatOperator` for string concatenation with variadic support
  - Fluent API support for concat operator in `ConditionBuilder`
  - Comprehensive unit tests for concat operator functionality
  - Integration tests for concat operator with rule engine

## [1.0.0] - 2025-12-29

### Added

- Initial release of PHP Rule Engine
- **Core Expression System**
  - `LiteralExpression` for constant values
  - `VariableExpression` for accessing context variables with dot notation support
  - `OperatorExpression` for combining expressions with operators
  - `ExpressionInterface` for defining expression contracts

- **Context Management**
  - `Context` class for managing evaluation context data
  - `ContextInterface` for context abstractions
  - Support for nested variable access via dot notation (e.g., `user.profile.age`)

- **Evaluator**
  - `Evaluator` for recursive expression evaluation
  - `EvaluatorInterface` for evaluator contracts
  - Full support for all operator types

- **Comprehensive Operator System**
  - **Comparison Operators**: `=`, `!=`, `<`, `>`, `<=`, `>=`, `===`, `!==`
    - `EqualOperator`
    - `NotEqualOperator`
    - `IdenticalOperator` (strict equality with `===`)
    - `NotIdenticalOperator` (strict inequality with `!==`)
    - `LessThanOperator`
    - `LessThanOrEqualOperator`
    - `GreaterThanOperator`
    - `GreaterThanOrEqualOperator`

  - **Logical Operators**: `AND`, `OR`, `NOT`, `XOR`
    - `AndOperator`
    - `OrOperator`
    - `NotOperator`
    - `XorOperator`

  - **Mathematical Operators**: `+`, `-`, `*`, `/`, `%`, `^`
    - `AddOperator`
    - `SubtractOperator`
    - `MultiplyOperator`
    - `DivideOperator`
    - `ModuloOperator`
    - `PowerOperator`
    - `DivisionByZeroException` for division operation safety

  - **Set Operators**: `IN`, `NOT_IN`, `CONTAINS`, `SUBSET`, `UNION`, `INTERSECT`, `DIFF`
    - `InOperator`
    - `NotInOperator`
    - `ContainsOperator`
    - `SubsetOperator`
    - `UnionOperator`
    - `IntersectOperator`
    - `DiffOperator`

  - **String Operators**: `STARTS_WITH`, `ENDS_WITH`, `CONTAINS_STRING`, `MATCHES`
    - `StartsWithOperator`
    - `EndsWithOperator`
    - `ContainsStringOperator`
    - `MatchesOperator` (regex support)
    - `InvalidRegexException` for invalid regex patterns

- **Operator Management**
  - `OperatorRegistry` for registering and retrieving operators
  - `OperatorRegistryInterface` for registry contracts
  - `AbstractOperator` base class for easy operator implementation
  - `OperatorInterface` for operator contracts

- **Rule System**
  - `Rule` class representing evaluable rules with metadata support
  - `RuleBuilder` for fluent rule construction
  - `ConditionBuilder` for chaining rule conditions with fluent API methods:
    - `identical($value)` - strict type and value equality check
    - `notIdentical($value)` - strict type and value inequality check
    - And all standard comparison operators
  - `RuleSet` for managing multiple rules
  - `RuleResult` for rule evaluation results
  - Support for rule metadata via `meta()` method

- **Rule Engine Facade**
  - `RuleEngine` main facade for easy engine usage
  - Fluent API for intuitive rule construction
  - Rule management (add, retrieve, remove rules)
  - Evaluation methods:
    - `evaluate($ruleName, $context)` - evaluate single rule
    - `evaluateAll($context)` - check if all rules pass
    - `evaluateAny($context)` - check if any rule passes
    - `getPassingRules($context)` - get rules that passed evaluation
    - `getFailingRules($context)` - get rules that failed evaluation
  - Custom operator registration

- **Serialization System**
  - `RuleSerializer` for converting rules to JSON format
  - `RuleDeserializer` for restoring rules from JSON
  - Round-trip serialization support
  - `DeserializationException` for deserialization errors

- **Exception Hierarchy**
  - `RuleEngineException` - base exception
  - `OperatorNotFoundException` - operator not found in registry
  - `RuleNotFoundException` - rule not found in engine
  - `VariableNotFoundException` - variable not found in context
  - `DivisionByZeroException` - division by zero attempt
  - `InvalidRegexException` - invalid regex in MATCHES operator
  - `DeserializationException` - JSON deserialization failures

- **Testing**
  - Comprehensive unit tests for all components
  - Integration tests for end-to-end scenarios
  - Real-world usage examples and test cases

- **Documentation**
  - [Operator Reference](docs/operators.md) - complete operator documentation
  - [Usage Examples](docs/examples.md) - real-world examples
  - README with quick start guide
  - Code examples for access control, e-commerce, form validation, and custom operators

- **Development Tools**
  - PHPUnit for testing
  - PHPStan (level 9) for static analysis
  - Rector for code refactoring
  - Docker support via Dockerfile and docker-compose.yml
  - Code formatting configuration (.editorconfig)

- **Project Files**
  - composer.json with dependencies and scripts
  - .gitignore for version control
  - .env.example for environment configuration
  - .gitattributes for consistent line endings
  - phpunit.xml.dist for test configuration
  - phpstan.neon.dist for static analysis configuration

[1.1.0]: https://github.com/funkyoz/rule-engine/releases/tag/v1.1.0
[1.0.0]: https://github.com/funkyoz/rule-engine/releases/tag/v1.0.0