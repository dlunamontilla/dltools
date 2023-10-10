<?php

namespace DLTools\Test;

use DLRoute\Config\Controller;
use DLRoute\Config\Test;
use DLRoute\Server\DLServer;
use DLTools\Compilers\DLView;
use DLTools\HttpRequest\SendMail;
use DLTools\Tests\Category;
use DLTools\Tests\Contacts;
use DLTools\Tests\Products;
use DLTools\Tests\Roles;
use DLTools\Tests\UsersTest\Users;

final class TestController extends Controller {

    /**
     * Método de ejecución del controlador.
     *
     * @param object $params
     * @return array
     */
    public function index(object $params): array {
        /**
         * Tipo MIME de archivo
         * 
         * @var string
         */
        $mime_type = "*/*";

        /**
         * Nombre de campo de archivos.
         * 
         * @var string
         */
        $field = "file";

        $this->set_thumbnail_width(300);

        /**
         * Datos de archivos.
         * 
         * @var array
         */
        $data = $this->upload_file($field, $mime_type);

        return [
            "message" => "Visto en pantalla",
            "filenames" => $data
        ];
    }

    /**
     * Realización de pruebas de obtención de lista de usuarios
     *
     * @param object $params
     * @return array
     */
    public function get_users(object $params): array {
        Users::set_order('users_id', 'desc');

        return [
            "products" => Products::paginate(1, 2),
            "users" => Users::get(),
            "paginate" => Users::paginate(1, 2),
            "count" => Users::count()
        ];
    }

    /**
     * Almacenar clientes en la base de datos
     *
     * @return array
     */
    public function save_clients(): array {
        /**
         * Clientes a almacenar
         * 
         * @var Clients
         */
        $clients = new Clients;

        $clients->client_name = $clients->get_input('name');
        $clients->client_lastname = $clients->get_input('lastname');
        $clients->client_email = $clients->get_input('email');

        return [
            "saved" => $clients->save(),
            "clients" => Clients::get()
        ];
    }

    public function clients_form(): string {
        return view('clients');
    }

    public function see_products(): string {
        new Category;

        /**
         * Categorías de productos.
         * 
         * @var array
         */
        $categories = Category::get();

        return view('products', [
            "categories" => $categories
        ]);
    }

    public function create_product(): array {
        $products = new Products;

        $created = Products::create([
            'product_name' => $products->get_input('name'),
            'product_description' => $products->get_input('description'),
            'users_ID' => 1,
            'category_ID' => 1
        ]);

        $products = Products::get();
        
        new Roles;

        return [
            'created' => $created,
            'products' => $products,
            'roles' => Roles::get()
        ];
    }

    /**
     * Probando la configuración de la conexión.
     *
     * @return void
     */
    public function test_config() {
        $test = Test::get_instance();
    }

    /**
     * Realización de pruebas
     *
     * @return array
     */
    public function users_test(): array {
        $users = new Users;
        $users->capture_credentials();

        return [
            $users->get_uuid('test')
        ];
    }

    public function create_contact(): array {
        $users = new Contacts;

        $users->contacts_name = $users->get_input('contacts_name');
        $users->contacts_email = $users->get_input('contacts_email');
        $users->countries_id = $users->get_input('countries_id');
        $users->dl_products_ID = $users->get_input('dl_products_ID');

        $users->save();

        return Contacts::get();
    }

    /**
     * Envío de correos electrónicos de prueba
     *
     * @return array
     */
    public function mail(): array {
        $email = new SendMail();

        return $email->send(
            $email->get_email('email'),
            $email->get_required('body')
        );
    }

    public function template(): string {

        ob_start();
        DLView::load('test', [
            'authenticted' => true,
            "files" => [],
            "uploads" => [],
            "info" => "Test",
            "query" => "query",
            "isValid" => false,
            "basedir" => DLServer::get_document_root(),
            "username" => "Usuario de prueba",
            "token" => "Token"
        ]);
        $view = ob_get_clean();

        return $view;
    }

    public function auth(): array {
        $users = new Users;

        $saved = $users->capture_credentials();
        
        if (!$saved) {
            http_response_code(401);
            
            return [
                "status" => false,
                "error" => "No se ha loggeado"
            ];
        }

        return [
            "status" => true,
            "success" => "Se ha loggeado"
        ];
    }
}
