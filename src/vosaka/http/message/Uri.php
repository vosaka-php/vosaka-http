<?php

declare(strict_types=1);

namespace vosaka\http\message;

use Psr\Http\Message\UriInterface;

use InvalidArgumentException;

final class Uri implements UriInterface
{
    private string $scheme = "";
    private string $userInfo = "";
    private string $host = "";
    private int|null $port = null;
    private string $path = "";
    private string $query = "";
    private string $fragment = "";

    public function __construct(string $uri)
    {
        $parts = parse_url($uri);
        if (
            $parts === false ||
            (isset($parts["scheme"]) &&
                !preg_match('#^[a-z]+$#i', $parts["scheme"])) ||
            (isset($parts["host"]) && preg_match("#[\s%+]#", $parts["host"]))
        ) {
            throw new InvalidArgumentException("Invalid URI given");
        }

        if (isset($parts["scheme"])) {
            $this->scheme = strtolower($parts["scheme"]);
        }

        if (isset($parts["user"])) {
            $this->userInfo =
                $this->encode($parts["user"], PHP_URL_USER) .
                (isset($parts["pass"])
                    ? ":" . $this->encode($parts["pass"], PHP_URL_PASS)
                    : "");
        }

        if (isset($parts["host"])) {
            $this->host = strtolower($parts["host"]);
        }

        if (
            isset($parts["port"]) &&
            !(
                ($parts["port"] === 80 && $this->scheme === "http") ||
                ($parts["port"] === 443 && $this->scheme === "https")
            )
        ) {
            $this->port = $parts["port"];
        }

        if (isset($parts["path"])) {
            $this->path = $this->encode($parts["path"], PHP_URL_PATH);
        }

        if (isset($parts["query"])) {
            $this->query = $this->encode($parts["query"], PHP_URL_QUERY);
        }

        if (isset($parts["fragment"])) {
            $this->fragment = $this->encode(
                $parts["fragment"],
                PHP_URL_FRAGMENT
            );
        }
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        if ($this->host === "") {
            return "";
        }

        return ($this->userInfo !== "" ? $this->userInfo . "@" : "") .
            $this->host .
            ($this->port !== null ? ":" . $this->port : "");
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withScheme(string $scheme): UriInterface
    {
        $scheme = strtolower($scheme);
        if ($scheme === $this->scheme) {
            return $this;
        }

        if (!preg_match('#^[a-z]*$#', $scheme)) {
            throw new InvalidArgumentException("Invalid URI scheme given");
        }

        $new = clone $this;
        $new->scheme = $scheme;

        if (
            ($this->port === 80 && $scheme === "http") ||
            ($this->port === 443 && $scheme === "https")
        ) {
            $new->port = null;
        }

        return $new;
    }

    public function withUserInfo(
        string $user,
        string|null $password = null
    ): UriInterface {
        $userInfo =
            $this->encode($user, PHP_URL_USER) .
            ($password !== null
                ? ":" . $this->encode($password, PHP_URL_PASS)
                : "");
        if ($userInfo === $this->userInfo) {
            return $this;
        }

        $new = clone $this;
        $new->userInfo = $userInfo;

        return $new;
    }

    public function withHost(string $host): UriInterface
    {
        $host = strtolower($host);
        if ($host === $this->host) {
            return $this;
        }

        if (
            preg_match("#[\s%+]#", $host) ||
            ($host !== "" &&
                parse_url("http://" . $host, PHP_URL_HOST) !== $host)
        ) {
            throw new InvalidArgumentException("Invalid URI host given");
        }

        $new = clone $this;
        $new->host = $host;

        return $new;
    }

    public function withPort(mixed $port): self
    {
        $port = $port === null ? null : (int) $port;
        if (
            ($port === 80 && $this->scheme === "http") ||
            ($port === 443 && $this->scheme === "https")
        ) {
            $port = null;
        }

        if ($port === $this->port) {
            return $this;
        }

        if ($port !== null && ($port < 1 || $port > 0xffff)) {
            throw new InvalidArgumentException("Invalid URI port given");
        }

        $new = clone $this;
        $new->port = $port;

        return $new;
    }

    public function withPath(string $path): UriInterface
    {
        $path = $this->encode($path, PHP_URL_PATH);
        if ($path === $this->path) {
            return $this;
        }

        $new = clone $this;
        $new->path = $path;

        return $new;
    }

    public function withQuery(string $query): UriInterface
    {
        $query = $this->encode($query, PHP_URL_QUERY);
        if ($query === $this->query) {
            return $this;
        }

        $new = clone $this;
        $new->query = $query;

        return $new;
    }

    public function withFragment(string $fragment): UriInterface
    {
        $fragment = $this->encode($fragment, PHP_URL_FRAGMENT);
        if ($fragment === $this->fragment) {
            return $this;
        }

        $new = clone $this;
        $new->fragment = $fragment;

        return $new;
    }

    private function encode(
        array|string $part,
        int $component
    ): array|string|null {
        return preg_replace_callback(
            '/(?:[^a-z0-9_\-\.~!\$&\'\(\)\*\+,;=' .
                ($component === PHP_URL_PATH
                    ? ":@\/"
                    : ($component === PHP_URL_QUERY ||
                    $component === PHP_URL_FRAGMENT
                        ? ":@\/\?"
                        : "")) .
                "%]++|%(?![a-f0-9]{2}))/i",
            function (array $match) {
                return rawurlencode($match[0]);
            },
            $part
        );
    }

    public static function resolve(
        UriInterface $base,
        UriInterface $rel
    ): UriInterface {
        if ($rel->getScheme() !== "") {
            return $rel->getPath() === ""
                ? $rel
                : $rel->withPath(self::removeDotSegments($rel->getPath()));
        }

        $reset = false;
        $new = $base;
        if ($rel->getAuthority() !== "") {
            $reset = true;
            $userInfo = explode(":", $rel->getUserInfo(), 2);
            $new = $base
                ->withUserInfo($userInfo[0], $userInfo[1] ?? null)
                ->withHost($rel->getHost())
                ->withPort($rel->getPort());
        }

        if ($reset && $rel->getPath() === "") {
            $new = $new->withPath("");
        } elseif (($path = $rel->getPath()) !== "") {
            $start = "";
            if ($path === "" || $path[0] !== "/") {
                $start = $base->getPath();
                if (substr($start, -1) !== "/") {
                    $start .= "/../";
                }
            }
            $reset = true;
            $new = $new->withPath(self::removeDotSegments($start . $path));
        }
        if ($reset || $rel->getQuery() !== "") {
            $reset = true;
            $new = $new->withQuery($rel->getQuery());
        }
        if ($reset || $rel->getFragment() !== "") {
            $new = $new->withFragment($rel->getFragment());
        }

        return $new;
    }

    private static function removeDotSegments(string $path): string
    {
        $segments = [];
        foreach (explode("/", $path) as $segment) {
            if ($segment === "..") {
                array_pop($segments);
            } elseif ($segment !== "." && $segment !== "") {
                $segments[] = $segment;
            }
        }
        return "/" .
            implode("/", $segments) .
            ($path !== "/" && substr($path, -1) === "/" ? "/" : "");
    }

    public function __toString(): string
    {
        $uri = "";
        if ($this->scheme !== "") {
            $uri .= $this->scheme . ":";
        }

        $authority = $this->getAuthority();
        if ($authority !== "") {
            $uri .= "//" . $authority;
        }

        if (
            $authority !== "" &&
            isset($this->path[0]) &&
            $this->path[0] !== "/"
        ) {
            $uri .= "/" . $this->path;
        } elseif (
            $authority === "" &&
            isset($this->path[0]) &&
            $this->path[0] === "/"
        ) {
            $uri .= "/" . ltrim($this->path, "/");
        } else {
            $uri .= $this->path;
        }

        if ($this->query !== "") {
            $uri .= "?" . $this->query;
        }

        if ($this->fragment !== "") {
            $uri .= "#" . $this->fragment;
        }

        return $uri;
    }
}
