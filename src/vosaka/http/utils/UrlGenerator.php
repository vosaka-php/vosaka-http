<?php

declare(strict_types=1);

namespace vosaka\http\utils;

use InvalidArgumentException;

final class UrlGenerator
{
    public function __construct(private array $namedRoutes) {}

    public function generate(
        string $name,
        array $params = [],
        array $query = []
    ): string {
        if (!isset($this->namedRoutes[$name])) {
            throw new InvalidArgumentException("Route '{$name}' not found");
        }

        $route = $this->namedRoutes[$name];
        $url = $this->buildUrl($route->pattern, $params);

        // Add query parameters if provided
        if (!empty($query)) {
            $url .= "?" . http_build_query($query);
        }

        return $url;
    }

    private function buildUrl(string $pattern, array $params): string
    {
        $url = $pattern;

        // Sort parameters by length (longest first) to avoid partial replacements
        uksort($params, fn($a, $b) => strlen($b) - strlen($a));

        // Replace constrained parameters first: {id:\d+}
        foreach ($params as $key => $value) {
            $url = preg_replace(
                "/\{" . preg_quote($key, "/") . ":[^}]+\}/",
                urlencode((string) $value),
                $url
            );
        }

        // Replace regular parameters: {id}
        foreach ($params as $key => $value) {
            $url = str_replace(
                "{" . $key . "}",
                urlencode((string) $value),
                $url
            );
        }

        // Remove optional parameters that weren't provided
        $url = preg_replace("/\{[^}]*\?\}/", "", $url);

        // Clean up any double slashes
        $url = preg_replace("#/+#", "/", $url);

        // Check for unreplaced required parameters
        if (preg_match("/\{[^}]+\}/", $url)) {
            preg_match_all("/\{([^}]+)\}/", $url, $missingParams);
            throw new InvalidArgumentException(
                "Missing required parameters: " .
                    implode(", ", $missingParams[1])
            );
        }

        return $url;
    }
}
