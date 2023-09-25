<?php

namespace DLTools\HttpRequest;

/**
 * @package DLTools
 * @version 1.0.0
 * @author David E Luna <davidlunamontilla@gmail.com>
 * @copyright (c) 2020 - David E Luna M
 * @license MIT
 */

class DLHost {
    private array $hostName = [];

    /**
     * Ingrese los nombres de hosts a los que se le obligarán a usar HTTPS
     *
     * @param array $hostName
     */
    public function __construct(array $hostName = []) {
        if (count($hostName) > 0) {
            foreach ($hostName as $host) {
                array_push($this->hostName, $host);
            }
        }
    }

    /**
     * Devuelve el nombre actual de host
     * 
     * @return string
     */
    public static function getHostname(): string {
        return $_SERVER['HTTP_HOST'] ?? '';
    }

    /**
     * Devuelve el dominio del sitio Web
     *
     * @return string
     */
    public static function getDomain(): string {
        $host = self::getHostname();
        $host = preg_replace("/:{1}[0-9]+$/", "", $host);

        return $host ?? '';
    }

    /**
     * Determina si el usuario está accediendo al sitio web
     * con el protocolo HTTPS activado o no.
     *
     * @return boolean
     */
    public static function isHTTPS(): bool {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    public function https(): void {
        $serverName = (string) strtolower($_SERVER['SERVER_NAME']);
        $https = self::isHTTPS();
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
