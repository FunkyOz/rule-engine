# PHP Rule Engine - Task Index

This directory contains all implementation tasks for building a comprehensive rule engine in PHP, supporting logical operators, mathematical operations, and set operations.

## Overview

**Total Tasks:** 18
**Current Status:** Planning Phase

---

## Phase 1: Foundation (Critical Priority)

Setup project infrastructure, define core interfaces and base abstractions.

| # | Task | Priority | Complexity | Status | Dependencies |
|---|------|----------|------------|--------|--------------|
| 01 | [Project Setup & Dependencies](01-project-setup.md) | Critical | Low | `done` | None |
| 02 | [Core Interfaces & Contracts](02-core-interfaces.md) | Critical | Medium | `done` | 01 |
| 03 | [Context & Variable System](03-context-system.md) | Critical | Medium | `done` | 02 |

**Deliverables:**
- Development environment configured (PHPStan, Pest, Pint)
- Core interfaces: `ExpressionInterface`, `OperatorInterface`, `EvaluatorInterface`
- Context system for variable resolution

---

## Phase 2: Expression Engine (Critical Priority)

Build the expression parsing and evaluation core.

| # | Task | Priority | Complexity | Status | Dependencies |
|---|------|----------|------------|--------|--------------|
| 04 | [Value Expressions](04-value-expressions.md) | Critical | Low | `done` | 02, 03 |
| 05 | [Expression Evaluator](05-expression-evaluator.md) | Critical | Medium | `done` | 04 |
| 06 | [Operator Registry](06-operator-registry.md) | Critical | Medium | `done` | 02 |

**Deliverables:**
- Literal and variable value expressions
- Expression evaluation engine
- Extensible operator registration system

---

## Phase 3: Operators Implementation (High Priority)

Implement all operator types: logical, comparison, math, and set operations.

| # | Task | Priority | Complexity | Status | Dependencies |
|---|------|----------|------------|--------|--------------|
| 07 | [Comparison Operators](07-comparison-operators.md) | High | Medium | `done` | 05, 06 |
| 08 | [Logical Operators](08-logical-operators.md) | High | Medium | `done` | 05, 06 |
| 09 | [Math Operators](09-math-operators.md) | High | Medium | `done` | 05, 06 |
| 10 | [Set Operators](10-set-operators.md) | High | Medium | `done` | 05, 06 |
| 11 | [String Operators](11-string-operators.md) | Medium | Low | `done` | 05, 06 |

**Deliverables:**
- Comparison: `=`, `!=`, `<`, `>`, `<=`, `>=`
- Logical: `AND`, `OR`, `NOT`, `XOR`
- Math: `+`, `-`, `*`, `/`, `%`, `^`
- Set: `IN`, `NOT IN`, `CONTAINS`, `INTERSECT`, `UNION`, `DIFF`, `SUBSET`
- String: `STARTS_WITH`, `ENDS_WITH`, `CONTAINS`, `MATCHES`

---

## Phase 4: Rule System (High Priority)

Build the rule definition, composition, and execution system.

| # | Task | Priority | Complexity | Status | Dependencies |
|---|------|----------|------------|--------|--------------|
| 12 | [Rule Definition](12-rule-definition.md) | High | Medium | `done` | 07, 08 |
| 13 | [Rule Builder (Fluent API)](13-rule-builder.md) | High | Medium | `done` | 12 |
| 14 | [Rule Engine Facade](14-rule-engine-facade.md) | High | Medium | `done` | 12, 13 |
| 15 | [Rule Serialization](15-rule-serialization.md) | Medium | Medium | `done` | 12 |

**Deliverables:**
- Rule class with conditions and actions
- Fluent API for rule construction
- Main RuleEngine class for rule evaluation
- JSON/Array serialization for rule storage

---

## Phase 5: Testing (High Priority)

Comprehensive test coverage for all components.

| # | Task | Priority | Complexity | Status | Dependencies |
|---|------|----------|------------|--------|--------------|
| 16 | [Unit Tests](16-unit-tests.md) | High | Medium | `done` | All previous |
| 17 | [Integration Tests](17-integration-tests.md) | High | Medium | `done` | 16 |

**Deliverables:**
- Unit tests for all operators and expressions
- Integration tests for rule evaluation
- Edge case and error handling coverage

---

## Phase 6: Documentation (Medium Priority)

Usage documentation and examples.

| # | Task | Priority | Complexity | Status | Dependencies |
|---|------|----------|------------|--------|--------------|
| 18 | [Documentation & Examples](18-documentation.md) | Medium | Low | `done` | All previous |

**Deliverables:**
- README with usage examples
- API documentation
- Example use cases

---

## Architecture Overview

```
src/
├── Expression/                # Expression system
│   ├── ExpressionInterface.php
│   ├── LiteralExpression.php
│   ├── VariableExpression.php
│   └── OperatorExpression.php
├── Operator/                  # Operator system
│   ├── OperatorInterface.php
│   ├── AbstractOperator.php
│   ├── Comparison/
│   │   ├── EqualOperator.php
│   │   ├── NotEqualOperator.php
│   │   └── ...
│   ├── Logical/
│   │   ├── AndOperator.php
│   │   ├── OrOperator.php
│   │   └── ...
│   ├── Math/
│   │   ├── AddOperator.php
│   │   ├── SubtractOperator.php
│   │   └── ...
│   ├── Set/
│   │   ├── InOperator.php
│   │   ├── ContainsOperator.php
│   │   └── ...
│   └── String/
│       ├── StartsWithOperator.php
│       └── ...
├── Context/                   # Context and variable resolution
│   ├── ContextInterface.php
│   └── Context.php
├── Registry/                  # Operator registry
│   ├── OperatorRegistryInterface.php
│   └── OperatorRegistry.php
├── Evaluator/                 # Expression evaluator
│   ├── EvaluatorInterface.php
│   └── Evaluator.php
├── Rule/                      # Rule system
│   ├── Rule.php
│   ├── RuleBuilder.php
│   └── RuleSet.php
├── Serialization/             # Serialization
│   ├── RuleSerializer.php
│   └── RuleDeserializer.php
├── Exception/                 # All exceptions
│   ├── RuleEngineException.php
│   ├── VariableNotFoundException.php
│   └── OperatorNotFoundException.php
└── RuleEngine.php             # Main facade
```

---

## Quick Reference

### Critical Path (MVP)
1. Project Setup (01)
2. Core Interfaces (02)
3. Context System (03)
4. Value Expressions (04)
5. Expression Evaluator (05)
6. Operator Registry (06)
7. Comparison Operators (07)
8. Logical Operators (08)
9. Rule Definition (12)
10. Rule Engine Facade (14)

### Task Status Legend
- `todo` - Not started
- `progress` - Currently being worked on
- `done` - Completed and tested

### Complexity Ratings
- **Low** - Straightforward implementation
- **Medium** - Moderate complexity
- **High** - Complex implementation

---

**Last Updated:** 2025-12-28
**Document Version:** 1.0
