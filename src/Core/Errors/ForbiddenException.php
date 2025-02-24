<?php

declare(strict_types=1);

namespace DLTools\Core\Errors;

use DLRoute\Requests\DLOutput;
use RuntimeException;
use Throwable;

final class ForbiddenException extends RuntimeException {
    /**
     * Inicializa una excepción de acceso prohibido.
     *
     * @param string $message Mensaje descriptivo del error (opcional).
     * @param int $code Código de estado HTTP asociado (opcional, por defecto 403).
     * @param Throwable|null $previous Excepción encadenada previa (opcional).
     */
    public function __construct(string $message = "Acceso prohibido", int $code = 403, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Genera una respuesta HTTP con el código 403 y devuelve el mensaje de error en formato JSON.
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
