<?php

namespace common\modules\tasks\commands;

trait TextArgumentCommandTrait
{
    protected function matchTextArgument(string $text, string $prefixPattern): ?string
    {
        $normalized = $this->normalizeCommandText($text);
        if ($normalized === '') {
            return null;
        }

        if (preg_match('/^' . $prefixPattern . '(?:\s+)(.+)$/ius', $normalized, $matches) !== 1) {
            return null;
        }

        return $this->unwrapQuotedArgument($matches[1] ?? null);
    }

    private function normalizeCommandText(string $text): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $normalized = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/[\p{Z}\s]+/u', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }

    private function unwrapQuotedArgument(?string $argument): ?string
    {
        $value = trim((string) $argument);
        if ($value === '') {
            return null;
        }

        $quotePatterns = [
            '/^"(.+)"$/us',
            '/^“(.+)”$/us',
            '/^„(.+)“$/us',
            '/^\'(.+)\'$/us',
            '/^«(.+)»$/us',
        ];

        foreach ($quotePatterns as $pattern) {
            if (preg_match($pattern, $value, $matches) !== 1) {
                continue;
            }

            $quotedValue = trim((string) ($matches[1] ?? ''));

            return $quotedValue !== '' ? $quotedValue : null;
        }

        return $value;
    }
}
