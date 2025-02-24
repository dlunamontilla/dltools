<?php

declare(strict_types=1);

namespace DLTools\Core\Errors;

use DLRoute\Requests\DLOutput;
use RuntimeException;
use Throwable;

/**
 * Excepción personalizada para argumentos inválidos.
 *
 * Esta excepción es lanzada cuando se proporciona un argumento no válido
 * en una función o método. Extiende de `RuntimeException` y permite generar
 * una respuesta HTTP en formato JSON.
 */
final class InvalidArgumentException extends RuntimeException {
    /**
     * Inicializa una excepción de argumento inválido.
     *
     * @param string $message Mensaje descriptivo del error (opcional, por defecto "Argumento inválido").
     * @param int $code Código de estado HTTP asociado (opcional, por defecto 400 Bad Request).
     * @param Throwable|null $previous Excepción encadenada previa (opcional).
     */
    public function __construct(string $message = "Argumento inválido", int $code = 400, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Genera una respuesta HTTP con el código de estado correspondiente y devuelve el mensaje de error en formato JSON.
     *
     * Esta función envía una cabecera `Content-Type: application/json`, establece el código de estado HTTP
     * de la respuesta y devuelve un mensaje estructurado en JSON con detalles del error.
     *
     * @return void
     */
    public function render(): void {
        header('Content-Type: application/json; charset=utf-8', true, $this->getCode());
        echo DLOutput::get_json([
            'status' => false,
            'error' => true,
            'message' => $this->getMessage(),
            'code' => $this->getCode()
        ], true);

        exit;
    }
}
