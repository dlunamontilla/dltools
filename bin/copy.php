
<?php

class Copy {
    public function __construct() {
    }

    public function files(string $source, string $dest, int $permissions = 0755): bool {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions, true);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            $this->files("$source/$entry", "$dest/$entry", $permissions);
        }

        // Clean up
        $dir->close();
        return true;
    }

    /**
     * Permite establecer las rutas de copia de archivos en otro nivel 
     * de directorio.
     *
     * @param string $path
     * @param integer $level
     * @return string
     */
    public function setPath(string $path, int $level = 1): string {
        $path = dirname(__FILE__, $level) . "/$path";
        return $path;
    }

    /**
     * Permite copiar archivos individuales que previamente se hayan seleccionados.
     * 
     * Uso:
     * 
     * ```php
     * <?php
     * $copy->copyConfig((object) [
     *      "files" => ["archivo1", "archivo2", "etc"],
     *      
     *      "source" => $copy->setPath("", 2),
     *      "target" => $copy->setPath("deploy/codejeran", 3)
     *  ]);
     * ```
     *
     * @param object $config
     * @return void
     */
    public function copyConfig(object $config): void {
        $files = $config->files ?? [];
        $source = trim($config->source ?? '');
        $target = trim($config->target ?? '');

        if (empty($source)) {
            throw new Error("Debe indicar dónde se encuentran los archivos a copiar");
        }

        if (empty($target)) {
            throw new Error("Debe definir dónde se van a copiar los archivos");
        }

        if (!file_exists($target)) {
            mkdir($target, 0755, true);
            echo $target . "\n";
        }

        foreach ($files as $file) {
            $sourceFile = $source . $file;
            $targetFile = $target . $file;

            $json = [];

            if (!file_exists($sourceFile)) {
                throw new Error("No todos los archivos seleccionados existen. Asegúrese de que haya escrito correctamente su nombre y/o su ubicación");
            }

            $type = $this->getMimeType($sourceFile);
            $isJSON = $type === "application/json";

            if ($isJSON) {
                $dataString = file_get_contents($sourceFile);
                $dataString = preg_replace("/app\//", "src\/", $dataString);

                $data = json_decode($dataString, true);

                foreach ($data as $key => $value) {

                    if ($key === "scripts" || $key === "bin") {
                        continue;
                    }

                    $json[$key] = $value;
                }

                $info = file_put_contents($targetFile, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                echo ($info !== FALSE)
                    ? "$sourceFile -> $targetFile\n"
                    : "No se puede copiar el archivo «$file\»\n";

                continue;
            }

            $info = copy($sourceFile, $targetFile);

            echo ($info)
                ? "$sourceFile-> $targetFile\n"
                : "No se puedo copiar el archivo «$file\»\n";
        }
    }

    /**
     * Permite saber el tipo mime de un archivo.
     *
     * @param string $file
     * @return void
     */
    public function getMimeType(string $file): string | FALSE {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file);
        finfo_close($finfo);
        return $mime_type;
    }
}

/**
 * Inicia la copia de archivos
 *
 * @return void
 */
function init() {
    $copy = new Copy;

    $info = $copy->files(
        $copy->setPath("dist", 2),
        $copy->setPath("deploy/codejeran/public", 3)
    );
    var_dump($info);

    $info = $copy->files(
        $copy->setPath("api", 2),
        $copy->setPath("deploy/codejeran/public/api", 3)
    );
    var_dump($info);

    $info = $copy->files(
        $copy->setPath("app", 2),
        $copy->setPath("deploy/codejeran/src", 3)
    );
    var_dump($info);

    $info = $copy->files(
        $copy->setPath("compiler", 2),
        $copy->setPath("deploy/codejeran/compiler", 3)
    );
    var_dump($info);

    $info = $copy->files(
        $copy->setPath("compiler", 2),
        $copy->setPath("deploy/codejeran/compiler", 3)
    );
    var_dump($info);

    $copy->copyConfig((object) [
        "files" => [
            ".env", ".gitignore", "composer.json", "composer.lock", "README.md", ".htaccess", ".prettierrc", "composer"
        ],


        "source" => $copy->setPath("", 2),
        "target" => $copy->setPath("deploy/codejeran/", 3)
    ]);

    $copy->copyConfig((object) [
        "files" => [
            "connect-database"
        ],

        "source" => $copy->setPath("bin/", 2),
        "target" => $copy->setPath("deploy/codejeran/bin/", 3)
    ]);

    echo "\n";
}

init();