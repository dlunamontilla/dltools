<?php

namespace DLTools\HttpRequest;

use DLRoute\Server\DLHost as ServerDLHost;

/**
 * Clase obsoleta para la gestión del host.
 * 
 * Esta clase está en desuso y será eliminada en futuras versiones. 
 * Se recomienda migrar a la clase `DLRoute\Server\DLHost`, que ofrece 
 * mejoras en la gestión de host y soporte actualizado para entornos HTTPS.
 *
 * @package DLTools
 * @version 1.1.0
 * @deprecated Esta clase está en desuso desde la versión v0.1.56.
 *             Se recomienda usar `DLRoute\Server\DLHost` en su lugar, ya que 
 *             esta clase será eliminada en versiones futuras.
 * 
 * @author David E Luna <davidlunamontilla@gmail.com>
 * @copyright (c) 2020 - David E Luna M
 * @license MIT
 */
class DLHost {
    /**
     * Lista de nombres de host que se redirigirán a HTTPS.
     *
     * @var array
     */
    private array $hostName = [];

    /**
     * Inicializa la clase con una lista de nombres de host que deben ser redirigidos a HTTPS.
     *
     * @param array $hostName Lista de nombres de host que se forzarán a HTTPS.
     */
    public function __construct(array $hostName = []) {
        if (count($hostName) > 0) {
            foreach ($hostName as $host) {
                array_push($this->hostName, $host);
            }
        }
    }

    /**
     * Devuelve el nombre actual de host.
     *
     * @return string Nombre del host actual.
     * 
     * @deprecated Este método está en desuso desde la versión v0.1.56.
     *             Utilice `DLRoute\Server\DLHost::get_hostname()` en su lugar.
     */
    public static function getHostname(): string {
        return ServerDLHost::get_hostname();
    }

    /**
     * Devuelve el dominio del sitio Web actual, sin el puerto.
     *
     * @return string Dominio actual sin el número de puerto.
     * 
     * @deprecated Este método está en desuso desde la versión v0.1.56.
     *             Utilice `DLRoute\Server\DLHost::get_domain()` en su lugar.
     */
    public static function getDomain(): string {
        return ServerDLHost::get_domain();
    }

    /**
     * Determina si el usuario está accediendo al sitio web con el protocolo HTTPS activado o no.
     *
     * @return bool `true` si el protocolo HTTPS está activo; de lo contrario, `false`.
     *
     * @deprecated Este método está en desuso desde la versión v0.1.56.
     *             Utilice `DLRoute\Server\DLHost::is_https()` en su lugar, ya que 
     *             este método se eliminará en futuras versiones.
     */
    public static function isHTTPS(): bool {
        return ServerDLHost::is_https();
    }

    /**
     * Redirige al usuario a la versión HTTPS del sitio si el nombre de host coincide
     * con uno de los nombres en la lista definida en el constructor.
     *
     * @return void
     * 
     * @deprecated Este método está en desuso desde la versión v0.1.56.
     *             Utilice la clase `DLRoute\Server\DLHost` para control de redirección HTTPS.
     */
    public function https(): void {
        $serverName = (string) strtolower($_SERVER['SERVER_NAME']);
        $https = \DLRoute\Server\DLHost::is_https();
        $url = (string) $_SERVER['REQUEST_URI'];

        if (!count($this->hostName) > 0)
            return;

        foreach ($this->hostName as $host) {
            if ($serverName === $host && !$https) {
                $url = "https://{$serverName}{$url}";
                header("Location: $url");
            }
        }
    }
}
