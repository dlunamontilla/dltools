<?php

namespace DLTools\Database;

/**
 * Ayuda a validar las entradas del usuario.
 * 
 * @package DLTools\Database
 * 
 * @version 1.0.0 (release)
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
trait DLQueryBuilder {
  
    /**
     * Establece el límite o rango de registros a devolver.
     *
     * @param integer $start Indica la cantidad de registros a mostrar si es la única con valor, de lo contrario, indica
     * desde dónde empezar.
     * @param integer|null $rows Opcional. Indica la cantidad de registros a mostrar.
     * @return static
     */
    public function limit(int $start, ?int $rows = null): static {
        if ($start < 0) {
            return $this;
        }

        $this->limit = $start;

        $is_range = $start >= 0 && (!is_null($rows) && $rows >= 0);

        if ($is_range) {
            $this->limit = "{$start}, {$rows}";
        }

        return $this;
    }

    /**
     * Todavía no disponible, pero se utilizará para unir tablas en el constructor de consultas
     *
     * @param string $table
     * @return self
     */
    public function inner(string $table): self {
        return $this;
    }

    /**
     * Devuelve una lista de registros por página
     *
     * @param integer $page
     * @param integer $rows
     * @return array
     */
    public function paginate(int $page, int $rows): array {

        if ($page < 1) {
            static::error("\$page debe ser mayor que cero (0)");
        }

        if ($rows < 1) {
            static::error("\$rows debe ser mayor que cero (0)");
        }

        /**
         * Identifica la cantidad de registros existentes.
         * 
         * @var array|integer $quantity
         */
        $quantity = $this->count();
        $quantity = $quantity['count'] ?? 0;

        /**
         * Cantidad de registro de comienzo.
         * 
         * @var int
         */
        $start = $rows * ($page - 1);

        /**
         * Número de paginas calculadas en función de la cantidad registros en la tabla
         * divida por la cantidad de registros por página (`$rows`).
         * 
         * @var integer
         */
        $pages = ceil($quantity / $rows);

        /**
         * Registro de la consulta.
         * 
         * @var array
         */
        $register = [];

        /**
         * Datos vacío.
         * 
         * @var array $empty_data
         */
        $empty_data = [
            "pages" => 1,
            "page" => 1,
            "pagination" => "1 de 1",
            "rows" => 0,

            "register" => []
        ];

        if ($quantity < 1) {
            return $empty_data;
        }

        if (count($register) < 1) {
            $register = $this->limit($start, $rows)->get();
        }

        if ($quantity <= 0) {
            $pages = 1;
            $rows = 0;
            $page = 1;
        }

        /**
         * Datos de la consulta.
         * 
         * @var array $data
         */
        $data = [
            "pages" => $pages,
            "page" => $page,
            "pagination" => "{$page} de {$pages}",
            "rows" => $rows,

            "register" => $register
        ];

        return $data;
    }
}