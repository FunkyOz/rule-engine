<?php

declare(strict_types=1);

namespace RuleEngine\Operator\String;

use RuleEngine\Exception\InvalidRegexException;
use RuleEngine\Operator\AbstractOperator;

final class MatchesOperator extends AbstractOperator
{
    public function __construct()
    {
        parent::__construct('MATCHES', 2);
    }

    public function execute(array $operands): bool
    {
        $this->validateOperandCount($operands);

        $subject = (string) $operands[0];
        $pattern = (string) $operands[1];

        // Suppress errors and check for false return
        $result = @preg_match($pattern, $subject);

        if ($result === false) {
            throw new InvalidRegexException($pattern, preg_last_error_msg());
        }

        return $result === 1;
    }
}
