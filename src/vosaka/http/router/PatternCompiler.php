<?php

declare(strict_types=1);

namespace vosaka\http\router;

use InvalidArgumentException;

final class PatternCompiler
{
    private array $cache = [];

    public function compile(string $pattern): CompiledRoute
    {
        // Use cache for performance
        if (isset($this->cache[$pattern])) {
            return $this->cache[$pattern];
        }

        $params = [];
        $regex = $pattern;
        $hasWildcard = false;

        // Handle wildcards first: /files/* -> captures everything after /files/
        if (str_ends_with($pattern, "/*")) {
            $regex = str_replace("/*", "/(?<wildcard>.*)", $regex);
            $params[] = "wildcard";
            $hasWildcard = true;
        }

        // Process parameter patterns step by step
        $segments = $this->parseSegments($regex, $pattern);
        $regexParts = $this->buildRegexParts($segments, $params);
        $finalRegex = "#^" . implode("", $regexParts) . '$#';

        $compiled = new CompiledRoute(
            $finalRegex,
            $params,
            $segments,
            $hasWildcard
        );
        $this->cache[$pattern] = $compiled;

        return $compiled;
    }

    private function parseSegments(
        string $regex,
        string $originalPattern
    ): array {
        $segments = [];
        $currentPos = 0;
        $patternLength = strlen($regex);

        while ($currentPos < $patternLength) {
            // Find next parameter start
            $paramStart = strpos($regex, "{", $currentPos);
            if ($paramStart === false) {
                // No more parameters, add rest as literal
                $segments[] = [
                    "type" => "literal",
                    "value" => substr($regex, $currentPos),
                ];
                break;
            }

            // Add literal part before parameter
            if ($paramStart > $currentPos) {
                $segments[] = [
                    "type" => "literal",
                    "value" => substr(
                        $regex,
                        $currentPos,
                        $paramStart - $currentPos
                    ),
                ];
            }

            // Find parameter end
            $paramEnd = strpos($regex, "}", $paramStart);
            if ($paramEnd === false) {
                throw new InvalidArgumentException(
                    "Unclosed parameter in pattern: {$originalPattern}"
                );
            }

            // Extract and parse parameter definition
            $paramDef = substr(
                $regex,
                $paramStart + 1,
                $paramEnd - $paramStart - 1
            );
            $currentPos = $paramEnd + 1;

            $segments[] = $this->parseParameterDefinition($paramDef);
        }

        return $segments;
    }

    private function parseParameterDefinition(string $paramDef): array
    {
        $isOptional = str_ends_with($paramDef, "?");
        if ($isOptional) {
            $paramDef = substr($paramDef, 0, -1);
        }

        $constraint = "[^/]+"; // default constraint
        if (str_contains($paramDef, ":")) {
            [$paramName, $constraint] = explode(":", $paramDef, 2);
        } else {
            $paramName = $paramDef;
        }

        return [
            "type" => "param",
            "name" => $paramName,
            "constraint" => $constraint,
            "optional" => $isOptional,
        ];
    }

    private function buildRegexParts(array $segments, array &$params): array
    {
        $regexParts = [];

        foreach ($segments as $segment) {
            if ($segment["type"] === "literal") {
                $regexParts[] = preg_quote($segment["value"], "#");
            } else {
                // parameter segment
                $params[] = $segment["name"];
                $namedGroup = "(?<{$segment["name"]}>{$segment["constraint"]})";
                if ($segment["optional"]) {
                    $regexParts[] = "(?:/{$namedGroup})?";
                } else {
                    $regexParts[] = $namedGroup;
                }
            }
        }

        return $regexParts;
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }
}
