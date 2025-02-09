<?php

declare(strict_types=1);

namespace DLTools\Database;

final class ParseSQL {

    /**
     * Consulta SQL completa
     *
     * @var string
     */
    private string $query;

    public function __construct(string $query) {
        $this->query = trim($query);
    }

    public function extract_where(): string {

        /** @var string $pattern */
        $pattern = "/where(.*)/i";

        /** @var bool $found */
        $found = boolval(preg_match($pattern, $this->query, $matches));

        /** @var string $query */
        $query = "";

        if ($found) {
            $query = $matches[0];
        }

        $query = preg_replace("/WHERE\s+/", "", $query);

        return $query;
    }

    /**
     * Extrae parte de la consulta por grupos
     *
     * @return array
     */
    public function extract_group(): array {
        /** @var string $query */
        $query = $this->extract_where();

        /** @var string[] $parts */
        $parts = explode(" OR ", $query);

        foreach ($parts as &$part) {
            if (!is_string($part)) {
                continue;
            }

            $part = trim($part);
        }

        return $parts;
    }

    public function get_query(): string {
        return $this->extract_where();
    }
}
