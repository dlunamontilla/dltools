<?php

use PHPUnit\Framework\TestCase;
use DLTools\Database\DLDatabase;

class DatabaseTest extends TestCase {
    private DLDatabase $database;

    /**
     * @before Inicializar el objeto $database
     *
     * @return void
     */
    public function setup(): void {
        $this->database = DLDatabase::get_instance();
    }

    /**
     * @test Evalúa la función `$this->database->from('tabla')` para asegurar de que funcione
     * correctamente.
     *
     * @return void
     */
    public function testFromAndSelect(): void {
        $tests = [
            [
                "expected" => "SELECT * FROM tabla",
                "actual" => $this->database->from("tabla")->get_query()
            ],

            [
                "expected" => "SELECT name, lastname FROM persons",
                "actual" => $this->database->from('persons')->select(["name", "lastname"])->get_query()
            ],

            [
                "expected" => "SELECT name, lastname FROM persons",
                "actual" => $this->database->from('persons')->select("name, lastname")->get_query()
            ],

            [
                "expected" => "SELECT product_name, product_type FROM products",
                "actual" => $this->database->select("product_name", "product_type")->from('products')->get_query()
            ],

            [
                "expected" => "SELECT product_name, product_type FROM products",
                "actual" => $this->database->select([
                    "product_name",
                    "product_type"
                ])->from('products')->get_query()
            ]
        ];

        $count = 0;

        foreach ($tests as $test) {
            ++$count;
            $test = (object) $test;
            $this->assertSame($test->expected, $test->actual);
        }
    }

    public function testInsert(): void {
        $tests = [
            [
                "expected" => "INSERT INTO `products` (`name`, `lastname`) VALUES (:name, :lastname)",
                "actual" => $this->database
                    ->to('products')
                    ->insert([
                        'name' => 'David',
                        'lastname' => 'Luna'
                    ], true)
            ],

            [
                "expected" => "INSERT INTO `products` (`name`, `lastname`) VALUES (:name, :lastname)",
                "actual" => $this->database
                    ->to('products')
                    ->insert([
                        [
                            'name' => "David Eduardo",
                            'lastname' => "Luna Montilla"
                        ],

                        [
                            'name' => "Juan Rafael",
                            'lastname' => "Luna Montilla"
                        ]
                    ], true)
            ]
        ];

        foreach ($tests as $test) {
            $test = (object) $test;
            $this->assertSame($test->expected, $test->actual, "No se encontraron coincidencias");
        }
    }

    /**
     * Probando Where
     *
     * @return void
     */
    public function testWhere(): void {
        $expected = "SELECT * FROM products WHERE name = :name";
        $actual = $this->database->from('products')->where('name', 'Cienca')->get_query();

        $this->assertSame($expected, $actual);
    }

    public function testLimit(): void {
        $expected = "SELECT name FROM products LIMIT 10";
        $actual = $this->database->select('name')->from('products')->limit(10)->get_query();

        $this->assertSame($expected, $actual);
    }

    public function testLimitWhere(): void {
        $expected = "SELECT name FROM products WHERE id = :id LIMIT 7";
        $actual = $this->database->select('name')->from('products')->where('id', 7)->limit(7)->get_query();

        $this->assertSame($expected, $actual);
    }

    public function testDeleteWhere(): void {
        $expected = "DELETE FROM products WHERE id = :id";
        $actual = $this->database->from('products')->where('id', 5)->delete(true);

        $this->assertSame($expected, $actual);
    }

    public function testDeleteWhereLike(): void {
        $this->database->clean();
        $expected = "DELETE FROM products WHERE name LIKE :name";
        $actual = $this->database->from('products')->where('name', 'like', 'valor')->delete(true);

        $this->assertSame($expected, $actual);
    }

    public function testDelete(): void {
        $this->database->clean();
        $expected = "DELETE FROM products";
        $actual = $this->database->from('products')->delete(true);

        $this->assertSame($expected, $actual);
    }

    public function testLast(): void {
        $this->database->clean();
        $expected = "SELECT * FROM products WHERE id = (SELECT MAX(id) FROM products) LIMIT 1";
        $actual = $this->database->from('products')->last('id', true);

        $this->assertSame($expected, $actual);
    }

    public function testMax(): void {
        $this->database->clean();
        $expected = "SELECT MAX(id) AS id FROM products";
        $actual = $this->database->from('products')->max('id', true);

        $this->assertSame($expected, $actual);

        $expected = "SELECT MAX(price) AS price FROM prendas";
        $actual = $this->database->from('prendas')->max('price', true);

        $this->assertSame($expected, $actual);
    }

    public function testMin(): void {
        $this->database->clean();
        $expected = 'SELECT MIN(id) AS id FROM products';
        $actual = $this->database->from('products')->min('id', true);

        $this->assertSame($expected, $actual);
    }

    public function testCount(): void {
        $this->database->clean();
        $expected = 'SELECT COUNT(*) AS count FROM products';
        $actual = $this->database->from('products')->count('*', true);

        $this->assertSame($expected, $actual);

        $expected = 'SELECT COUNT(name) AS name FROM users';
        $actual = $this->database->from('users')->count('name', true);

        $this->assertSame($expected, $actual);
    }

    public function testorder_by(): void {
        $this->database->clean();
        $expected = "SELECT * FROM products ORDER BY name";
        $actual = $this->database->from('products')->order_by('name')->get_query();

        $this->assertSame($expected, $actual);
    }

    public function testAsc(): void {
        $this->database->clean();
        $expected = "SELECT * FROM products ORDER BY name ASC";
        $actual = $this->database->from('products')->order_by('name')->asc()->get_query();

        $this->assertSame($expected, $actual);
    }

    public function testDesc(): void {
        $this->database->clean();
        $expected = "SELECT * FROM products ORDER BY name DESC";
        $actual = $this->database->from('products')->order_by('name')->desc()->get_query();

        $this->assertSame($expected, $actual);
    }

    public function testUpdate(): void {
        $this->database->clean();

        $expected = "UPDATE products SET name = :name";
        $actual = $this->database->from('products')->update([
            "name" => "Algún valor"
        ], true);

        $this->assertSame($expected, $actual);
    }

    public function testUpdateWhere(): void {
        $this->database->clean();

        $expected = "UPDATE products SET name = :name_v WHERE name = :name";
        $actual = $this->database->from('products')->where('name', 'Algún valor')->update([
            "name" => "Algún valor"
        ], true);

        $this->assertSame($expected, $actual);
    }

    public function testFromWhere(): void {
        $expected = 'SELECT * FROM product WHERE username = :username OR email = :email';

        $actual = $this->database->from('product')
                ->where('username', 'usuario')
                ->where('email', '=', 'david', 'or')
                ->get_query();

        $this->assertSame($expected, $actual);
    }

    public function testCustomerQuery(): void {
        $expected = "SELECT * FROM Tabla WHERE username = :username OR email = :email";
        $actual = $this->database->query("SELECT * FROM Tabla WHERE username = :username OR email = :email")->get_query();

        $this->assertSame($expected, $actual);
    }
}
