<?php

declare(strict_types=1);

namespace DLTools\Core\Data\DTO;

/**
 * Representa un rango de valores utilizado en consultas SQL con BETWEEN.
 *
 * Esta clase es una inyección de dependencia para el método `between`
 * del constructor de consultas en DLTools.
 *
 * @author David E Luna M.
 * @license MIT
 * @version 1.0.0
 * @since 25 de febrero de 2025
 * @link https://github.com/dlunamontilla/dltools/blob/main/src/Core/Data/Values/ValueRange.php
 */
final class ValueRange {
    /**
     * Valor de punto de partida del rango.
     *
     * @var string $from
     */
    public readonly string $from;

    /**
     * Valor de finalización del rango.
     *
     * @var string $to
     */
    public readonly string $to;

    /**
     * Constructor de la clase ValueRange.
     *
     * @param string $from Valor inicial del rango.
     * @param string $to Valor final del rango.
     */
    public function __construct(string $from, string $to) {
        $this->from = trim($from);
        $this->to = trim($to);
    }
}
