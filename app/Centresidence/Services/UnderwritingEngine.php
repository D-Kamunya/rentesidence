<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\UnderwritingRule;

/**
 * Configurable underwriting rules engine (handbook §9.2.3 / §9.7 step 3).
 *
 * Evaluates a partner product's rules against an application context (occupancy,
 * cashflow, obligations, …). Rules are data: a parameter, an operator and a
 * threshold. Hard-rule failures block submission; soft-rule failures are
 * warnings that allow continuation. The engine itself sources nothing — the
 * caller supplies the context values, keeping it module- and data-agnostic.
 */
class UnderwritingEngine
{
    /**
     * @param  array<string,mixed>  $context  parameter => value
     * @return array{passed:bool, hard_failures:array, soft_warnings:array, results:array}
     */
    public function evaluate(FinancePartnerModule $partnerModule, array $context): array
    {
        $hardFailures = [];
        $softWarnings = [];
        $results = [];

        foreach ($partnerModule->underwritingRules as $rule) {
            $actual = $context[$rule->parameter] ?? null;
            $passed = $this->passes($rule, $actual);

            $line = [
                'rule_name' => $rule->rule_name,
                'parameter' => $rule->parameter,
                'operator' => $rule->operator,
                'value' => $rule->value,
                'actual' => $actual,
                'is_hard_rule' => $rule->is_hard_rule,
                'passed' => $passed,
                'message' => $passed ? null : ($rule->error_message ?: "Failed {$rule->rule_name}"),
            ];
            $results[] = $line;

            if (! $passed) {
                if ($rule->is_hard_rule) {
                    $hardFailures[] = $line;
                } else {
                    $softWarnings[] = $line;
                }
            }
        }

        return [
            'passed' => empty($hardFailures),
            'hard_failures' => $hardFailures,
            'soft_warnings' => $softWarnings,
            'results' => $results,
        ];
    }

    private function passes(UnderwritingRule $rule, $actual): bool
    {
        // "required" only checks presence/truthiness.
        if ($rule->operator === UnderwritingRule::OP_REQUIRED) {
            return $actual !== null && $actual !== '' && $actual !== false;
        }

        // Any other operator needs a value to compare against.
        if ($actual === null) {
            return false;
        }

        switch ($rule->operator) {
            case UnderwritingRule::OP_GTE:
                return (float) $actual >= (float) $rule->value;

            case UnderwritingRule::OP_LTE:
                return (float) $actual <= (float) $rule->value;

            case UnderwritingRule::OP_EQ:
                return is_numeric($actual) && is_numeric($rule->value)
                    ? (float) $actual === (float) $rule->value
                    : (string) $actual === (string) $rule->value;

            case UnderwritingRule::OP_BETWEEN:
                [$min, $max] = array_pad(array_map('trim', explode(',', (string) $rule->value)), 2, null);

                return $min !== null && $max !== null
                    && (float) $actual >= (float) $min
                    && (float) $actual <= (float) $max;

            default:
                return false;
        }
    }
}
