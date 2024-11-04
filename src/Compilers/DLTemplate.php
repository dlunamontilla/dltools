<?php

namespace DLTools\Compilers;

use DLTools\Auth\DLAuth;


/**
 * Parsea las plantillas definidas en el directorio resources.
 * 
 * @package DLTools
 * 
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @license MIT
 * @version v0
 */
class DLTemplate {
    /**
     * Instancia de la clase DLTemplate
     *
     * @var self|null
     */
    private static ?self $instance = NULL;

    private function __construct() {
    }

    /**
     * Dobles llaves, que serán reemplazadas por entidades PHP.
     *
     * @param string $stringTemplate
     * @return string
     */
    private static function keys(string $stringTemplate): string {
        $search = '/\{\{ \$(.*?) \}\}/';
        $replace = '<?= htmlspecialchars(print_r($$1, true)); ?>';

        return preg_replace($search, $replace, $stringTemplate);
    }

    /**
     * Devuelve una la función `json_encode` con los parámetros establecidos
     * para que puedas devolver a partir de un Array una cadena JSON formateada.
     *
     * @param string $stringTemplate
     * @return string
     */
    private static function convertStringArrayToJSONPretty(string $stringTemplate): string {
        $search = '/@json\((.*?),?\s?(\'|\")pretty(\'|\")\)/';
        $replace = '<?= json_encode($1, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>';
        return preg_replace($search, $replace, $stringTemplate);
    }

    /**
     * Devuelve una función `json_encode` con un $array pasado como argumento
     *
     * @param string $stringTemplate Cadena a ser procesada
     * @return string
     */
    private static function convertStringArrayToJSON(string $stringTemplate): string {
        $search = '/@json\((.*?)\)/';
        $replace = '<?= json_encode($1); ?>';
        return preg_replace($search, $replace, $stringTemplate);
    }

    /**
     * Una llave de apertura y cierre con dos (02) signos de admiración
     * de cierre que serán reemplazadas por entidades PHP sin filtros.
     *
     * @param string $stringTemplate Código stringTemplate de la plantilla con sus directivas
     * @return string
     */
    private static function keysHTML(string $stringTemplate): string {
        $search = '/\{\!\! \$(.*?) \!\!\}/m';
        $replace = '<?= print_r(trim($$1), true); ?>';

        return preg_replace($search, $replace, $stringTemplate);
    }

    /**
     * Parsear las directivas de las estructuras condicionales
     *
     * @param string $stringTemplate
     * @return string
     */
    private static function parserConditionals(string $stringTemplate): string {
        $conditionlsOpen = '/@if{1}.*\n*$/mi';
        $conditionalsClose = '/\@endif|\@endif\n$/mi';
        $else = '/(@else)+\s*if+\s*/m';

        $stringTemplate = trim($stringTemplate);

        $stringTemplate = preg_replace_callback($conditionlsOpen, function ($matches) {
            $found = $matches[0];
            $if = trim(trim($found, "@"));

            $if = "<?php $if { ?>";
            return trim($if);
        }, $stringTemplate);

        $stringTemplate = preg_replace_callback($conditionalsClose, function (array $matches) {
            $found = $matches[0];
            $endif = str_replace($found, "<?php } ?>", $found);
            return trim($endif);
        }, $stringTemplate);

        $stringTemplate = self::parseElse($stringTemplate);
        $stringTemplate = self::parseElseIf($stringTemplate);

        return $stringTemplate;
    }

    /**
     * Parsea la estructura else
     *
     * @param string $stringTemplate
     * @return string
     */
    public static function parseElse(string $stringTemplate): string {
        $pattern = '/@else{1}$/';
        $replace = "<?php } else { ?>";

        $lines = preg_split("/\n/", $stringTemplate);
        $newLines = [];

        foreach ($lines as $key => $line) {
            $newLine = preg_replace($pattern, $replace, trim($line));
            array_push($newLines, $newLine);
        }

        return implode("\n", $newLines);
    }

    /**
     * Parsea la estructura elseif
     *
     * @param string $stringTemplate
     * @return string
     */
    public static function parseElseIf(string $stringTemplate): string {
        $pattern = '/@(else\s*if)\s*(.*)?\)/';
        $replace = "<?php } $1 $2) { ?>";

        $lines = preg_split("/\n/", $stringTemplate);
        $newLines =  [];

        foreach ($lines as $key => $line) {
            $newLine = preg_replace($pattern, $replace, trim($line));
            array_push($newLines, $newLine);
        }

        return implode("\n", $newLines);
    }

    /**
     * Parsea una estructura repetitida utiliza para
     * iterar arrays
     *
     * @param string $stringTemplate
     * @return string
     */
    private static function makeForEach(string $stringTemplate): string {
        $findFor = '/\@foreach.*\n*/mi';
        $endfor = '/\@endforeach.*\n*/mi';


        $stringTemplate = preg_replace_callback($findFor, function (array $matches) {
            $found = $matches[0];
            $for = trim(trim($found, '@'));

            $php = "<?php $for: ?>";
            return trim($php);
        }, $stringTemplate);

        $stringTemplate = preg_replace_callback($endfor, function (array $matches) {
            $found = $matches[0];
            $end = trim(trim($found, '@'));

            $php = "<?php $end; ?>";
            return trim($php);
        }, $stringTemplate);


        return trim($stringTemplate);
    }

    /**
     * Itera estructuras repetitivas
     *
     * @param string $stringTemplate
     * @return string
     */
    private static function makeFor(string $stringTemplate): string {
        $findFor = '/\@for.*\n*/mi';
        $endfor = '/\@endfor.*\n*/mi';


        $stringTemplate = preg_replace_callback($findFor, function (array $matches) {
            $found = $matches[0];
            $for = trim(trim($found, '@'));

            $php = "<?php $for: ?>";
            return trim($php);
        }, $stringTemplate);

        $stringTemplate = preg_replace_callback($endfor, function (array $matches) {
            $found = $matches[0];
            $end = trim(trim($found, '@'));

            $php = "<?php $end; ?>";
            return trim($php);
        }, $stringTemplate);


        return trim($stringTemplate);
    }

    /**
     * Crea etiquetas de apertura y cierre de PHP a partir
     * de las directivas @php y @endphp de Laravel
     *
     * @param string $stringTemplate
     * @return string
     */
    private static function makePHP(string $stringTemplate): string {
        $stringTemplate = preg_replace("/\@php/", "<?php", $stringTemplate);
        $stringTemplate = preg_replace("/\@endphp/", "?>", $stringTemplate);

        return trim($stringTemplate);
    }


    /**
     * Compila las plantillas dl-template a PHP
     *
     * @param string $stringTemplate
     * @return string
     */
    public static function build(string $stringTemplate): string {
        $stringTemplate = self::parseComments($stringTemplate);

        $stringTemplate = self::keys($stringTemplate);
        $stringTemplate = self::keysHTML($stringTemplate);
        $stringTemplate = self::parserConditionals($stringTemplate);
        $stringTemplate = self::makeForEach($stringTemplate);
        $stringTemplate = self::makeFor($stringTemplate);
        $stringTemplate = self::makePHP($stringTemplate);
        $stringTemplate = self::convertStringArrayToJSONPretty($stringTemplate);
        $stringTemplate = self::convertStringArrayToJSONPretty($stringTemplate);
        $stringTemplate = self::convertStringArrayToJSON($stringTemplate);
        $stringTemplate = self::parseIncludes($stringTemplate);
        $stringTemplate = self::parsePrint($stringTemplate);
        $stringTemplate = self::generateTokenCSRF($stringTemplate);
        $stringTemplate = self::parseMarkdown($stringTemplate);

        $stringTemplate = self::parseFunctions($stringTemplate);
        $stringTemplate = self::parse_break($stringTemplate);
        $stringTemplate = self::parse_continue($stringTemplate);
        $stringTemplate = self::parse_var($stringTemplate);

        return $stringTemplate;
    }

    /**
     * Devuelve la instancia del objeto. Si no existe la 
     * instancia, la crea.
     *
     * @return self
     */
    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Permite parsear la directiva que permite extender la vista base
     *
     * @param string $stringTemplate
     * @param array $data
     * @return string
     */
    public static function parseDirective(string $stringTemplate, array $data = []): string {
        $search = '@base(\'home\')';
        $search = "/@base\((.*)?\)/";

        $replace = '<?php DLTools\Compilers\DLView::load($1, $data); ?>';

        preg_match_all($search, $stringTemplate, $matches);
        $stringTemplate = preg_replace($search, "", $stringTemplate);
        $stringTemplate = self::parseSections($stringTemplate);

        /**
         * Rutas incluidas a partir de directivas principales
         * 
         * @var array
         */
        $includes = [];

        foreach ($matches[0] as $key => $value) {
            $string = preg_replace($search, $replace, $value);
            array_push($includes, $string);
        }


        $stringTemplate .= "\n\n" . implode("\n", $includes);
        return $stringTemplate;
    }

    /**
     * Parsea las secciones de las plantillas y las convierte en variables
     * con contenido establecidos en dichas secciones.
     * 
     *
     * @param string $stringTemplate
     * @return string
     */
    private static function parseSections(string $stringTemplate): string {
        $pattern = '/@section\((.*?)\)\s*([\s\S]*?)\s*@endsection/';

        preg_match_all($pattern, $stringTemplate, $matches);
        $stringTemplate = preg_replace($pattern, "", $stringTemplate);

        /**
         * Bloque de secciones
         * 
         * @var array
         */
        $blocks = $matches[0];

        $newBlocks = [];

        foreach ($blocks as $key => $block) {
            $block = trim($block);
            if (empty($block)) continue;

            $pattern = '/@section\((.*?)\)/';
            $replace = "<?php ob_start(); ?>";

            preg_match($pattern, $block, $matches);
            $s1 = $matches[1] ?? '';
            $s1 = str_replace("'", "", $s1);
            $s1 = trim($s1);

            if (empty($s1)) continue;

            $block = preg_replace($pattern, $replace, $block);

            $pattern = '/@endsection/';
            $replace = "<?php \$$s1 = ob_get_clean(); ?>\n<?php \$data['$s1'] = $$s1; ?>\n";

            $block = preg_replace($pattern, $replace, $block);

            array_push($newBlocks, $block);
        }

        $string = implode("", $newBlocks);

        $string = trim($string);
        $stringTemplate = trim($stringTemplate);

        return $string . $stringTemplate;
    }

    /**
     * Parsea la directa `@includes`
     *
     * @param string $stringTemplate
     * @return string
     */
    public static function parseIncludes(string $stringTemplate): string {
        $pattern = "/@includes\((.*?)\)/";
        $replace = "<?php DLTools\Compilers\DLView::load($1, \$data); ?>";

        $stringTemplate = preg_replace($pattern, $replace, $stringTemplate);
        return $stringTemplate;
    }

    /**
     * Parsea la directiva @print
     *
     * @param string $stringTemplate
     * @return string
     */
    public static function parsePrint(string $stringTemplate): string {
        $pattern = "/@print\((.*?)\)/";

        $stringTemplate = preg_replace_callback($pattern, function ($matches) use ($stringTemplate) {
            $m1 = $matches[1] ?? '';

            $m1 = trim($m1, '\'');
            $m1 = trim($m1);

            $section = $m1;
            $m1 = preg_replace("/\s/", "_", $m1);

            return empty(trim($m1))
                ? ''
                : "<?php if (!isset($$m1)) {echo \"<h3 style=\\\"color: white; background-color: #d00000; padding: 20px; border-radius: 5px; font-weight: normal\\\">No existe las sección <strong style=\\\"padding: 10px; border-radius: 5px; background-color: #000000a0\\\">$section</strong></h3>\"; http_response_code(500); exit(1);} print_r($$m1); ?>";
        }, $stringTemplate);

        return trim($stringTemplate);
    }

    /**
     * Elimina todos los comentarios. La estructura de
     * comentarios es la siguiente:
     * 
     * ```
     * {{-- ... --}}
     *```
     *
     * @param string $stringTemplate
     * @return string
     */
    public static function parseComments(string $stringTemplate): string {
        $pattern = "/\{\{\-\-([\s\S]*?)\-\-\}\}/";
        $stringTemplate = preg_replace($pattern, "", $stringTemplate);

        $pattern = "/<\!\-\-([\s\S]*?)\-\->/";
        $stringTemplate = preg_replace($pattern, "", $stringTemplate);

        return trim($stringTemplate);
    }

    /**
     * Devuelve un elemento de formulario de tipo `hidden` con un
     * valor que es el token de referencia. 
     *
     * @param string $stringTemplate
     * @return string
     */
    private static function generateTokenCSRF(string $stringTemplate): string {
        $stringTemplate = self::generateTokenCSRFWithField($stringTemplate);

        /**
         * Patrón de busca de la directiva @csrf
         * 
         * @var string $pattern
         */
        $pattern = "/@csrf/";

        $auth = DLAuth::get_instance();
        $token = $auth->get_token();

        $replace = "<input type=\"hidden\" name=\"csrf-token\" id=\"csrf-token\" value=\"{$token}\" />";
        $stringTemplate = preg_replace($pattern, $replace, $stringTemplate) ?? $stringTemplate;

        return $stringTemplate;
    }

    /**
     * Permite establecer un nombre personalizado al campo oculto del token CSRF.
     *
     * @param string $stringTemplate
     * @return string
     */
    private static function generateTokenCSRFWithField(string $stringTemplate): string {
        /**
         * Autenticador del sistema
         * 
         * @var DLAuth $auth
         */
        $auth = DLAuth::get_instance();

        /**
         * Token del sistema
         * 
         * @var string $token
         */
        $token = $auth->get_token();

        /**
         * Patrón de búsqueda de la directiva @csrf
         * 
         * @var string $pattern
         */
        $pattern = '/\@csrf\(\"(.*)\"\)/';

        /**
         * Valor de reemplazo.
         * 
         * @var string
         */
        $replace = "<input type=\"hidden\" name=\"$1\" id=\"$1\" value=\"{$token}\" />";

        $stringTemplate = preg_replace($pattern, $replace, $stringTemplate);

        $pattern = '/\@csrf\(\'(.*)\'\)/';

        $stringTemplate = preg_replace($pattern, $replace, $stringTemplate);

        return trim($stringTemplate);
    }

    /**
     * Ubica el archivo Markdown y lo compila
     *
     * @param string $stringTemplate
     * @return string
     */
    public static function parseMarkdown(string $stringTemplate): string {
        $pattern = "/@markdown\((.*?)\)/";
        $replace = "<?php echo \DLTools\Compilers\DLMarkdown::parse($1); ?>";

        return preg_replace($pattern, $replace, $stringTemplate) ?? $stringTemplate;
    }

    /**
     * Parsea todo lo que no se haya parseado entre llaves (`{{... }}`)
     *
     * @param string $stringTemplate
     * @return string
     */
    public static function parseFunctions(string $stringTemplate): string {
        $pattern = '/\{\{\s*(.*?)\s*\}\}/';
        $replace = "<?= $1; ?>";

        return preg_replace($pattern, $replace, $stringTemplate);
    }

    /**
     * Parsea la directiva @continue a <?php continue; ?>
     *
     * @return string
     */
    public static function parse_continue(string $input): string {
        $pattern = self::get_directive_parse("continue");
        $replace = "<?php continue; ?>";

        return preg_replace($pattern, $replace, $input);
    }

    /**
     * Traduce la directiva `@break` a `<?php break; ?>`
     *
     * @return string
     */
    public static function parse_break(string $input): string {
        $pattern = self::get_directive_parse('break');
        return preg_replace($pattern, '<?php break; ?>', $input);
    }

    /**
     * Traduce la directiva `@varname('variable', 'Valor de la variable)` a `$name = "Valor de la variable";
     * 
     * > Advertencia: la directiva `@varname` se encuentra en fase experimental.
     *
     * @param string $input
     * @return string
     */
    public static function parse_var(string $input): string {
        $pattern = "/(?<!\S)(\@varname\(([a-z]+), ((.*?))\))(?!\S)/";
        $replace = "<?php \$$2 = $3; ?>";

        return trim(preg_replace($pattern, $replace, $input));
    }

    /**
     * Devuelve la expresión regular de la directiva
     *
     * @param string $input Nombre de la directiva a ser procesada
     * @return string
     */
    public static function get_directive_parse(string $input): string {
        return "/(?<!\S)\@{$input}(?!\S)/";
    }
}
