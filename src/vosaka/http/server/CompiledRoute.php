<?php

declare(strict_types=1);

namespace vosaka\http\server;

/**
 * Compiled route pattern for efficient matching
 */
readonly class CompiledRoute
{
    public function __construct(
        public string $regex,
        public array $params,
        public array $constraints = [],
        public bool $hasWildcard = false
    ) {}

    /**
     * Get parameter names
     */
    public function getParameterNames(): array
    {
        return $this->params;
    }

    /**
     * Check if route has specific parameter
     */
    public function hasParameter(string $name): bool
    {
        return in_array($name, $this->params);
    }

    /**
     * Get regex pattern
     */
    public function getRegex(): string
    {
        return $this->regex;
    }

    /**
     * Check if this is a wildcard route
     */
    public function isWildcard(): bool
    {
        return $this->hasWildcard;
    }

    /**
     * Debug: Show compiled pattern info
     */
    public function debug(): array
    {
        return [
            "regex" => $this->regex,
            "params" => $this->params,
            "constraints" => $this->constraints,
            "hasWildcard" => $this->hasWildcard,
        ];
    }
}
