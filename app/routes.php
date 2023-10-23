<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;


return function (App $app) {
    //get
    $app->get('/customer/{id}', function (Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);
        $customerId = $args['id'];
    
        $query = $db->prepare('SELECT * FROM Customer WHERE CustomerID = ?');
        $query->execute([$customerId]);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
    
        if (count($results) > 0) {
            $response->getBody()->write(json_encode($results[0]));
        } else {
            $response->getBody()->write(json_encode(['message' => 'Customer not found']));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });

    // post data
    $app->post('/customers', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        $customerId = $parsedBody["customerId"];
        $namaCustomer = $parsedBody["namaCustomer"];
        $alamatCustomer = $parsedBody["alamatCustomer"];
        $noTelpCustomer = $parsedBody["noTelpCustomer"];
    
        $db = $this->get(PDO::class);
    
        try {
            $db->beginTransaction();
    
            $query = $db->prepare('INSERT INTO Customer (CustomerID, NamaCustomer, AlamatCustomer, TeleponCustomer) VALUES (?, ?, ?, ?)');
            $query->execute([$customerId, $namaCustomer, $alamatCustomer, $noTelpCustomer]);
    
            $db->commit();
    
            $response->getBody()->write(json_encode(['message' => 'Pelanggan berhasil ditambahkan']));
        } catch (PDOException $e) {
            $db->rollBack();
            $response->getBody()->write(json_encode(['message' => 'Penambahan Pelanggan Gagal']));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });

    //put data
    $app->put('/customers/{id}', function (Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();
        $customerId = $args['id'];
        $namaCustomer = $parsedBody["namaCustomer"];
        $alamatCustomer = $parsedBody["alamatCustomer"];
        $noTelpCustomer = $parsedBody["noTelpCustomer"];
    
        $db = $this->get(PDO::class);
    
        try {
            $db->beginTransaction();
    
            $query = $db->prepare('UPDATE Customer SET NamaCustomer = ?, AlamatCustomer = ?, TeleponCustomer = ? WHERE CustomerID = ?');
            $query->execute([$namaCustomer, $alamatCustomer, $noTelpCustomer, $customerId]);
    
            $db->commit();
    
            $response->getBody()->write(json_encode(['message' => 'Data pelanggan dengan ID ' . $customerId . ' telah diupdate']));
        } catch (PDOException $e) {
            $db->rollBack();
            $response->getBody()->write(json_encode(['message' => 'Pengubahan Data Pelanggan Gagal']));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });

    // delete data
    $app->delete('/customers/{id}', function (Request $request, Response $response, $args) {
        $currentId = $args['id'];
        $db = $this->get(PDO::class);
    
        try {
            $db->beginTransaction();
    
            $query = $db->prepare('DELETE FROM Customer WHERE CustomerID = ?');
            $query->execute([$currentId]);
    
            $db->commit();
    
            if ($query->rowCount() === 0) {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(['message' => 'Data tidak ditemukan']));
            } else {
                $response->getBody()->write(json_encode(['message' => 'Customer dengan ID ' . $currentId . ' dihapus dari database']));
            }
        } catch (PDOException $e) {
            $db->rollBack();
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Database error ' . $e->getMessage()]));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });
    
    
    // get by id
    $app->get('/countries', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('SELECT * FROM countries');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader("Content-Type", "application/json");
    });

    // get by id
    $app->get('/countries/{id}', function (Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);

        $query = $db->prepare('SELECT * FROM countries WHERE id=?');
        $query->execute([$args['id']]);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results[0]));

        return $response->withHeader("Content-Type", "application/json");
    });

    // post data
    $app->post('/countries', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();

        $id = $parsedBody["id"]; // menambah dengan kolom baru
        $countryName = $parsedBody["name"];

        $db = $this->get(PDO::class);

        $query = $db->prepare('INSERT INTO countries (id, name) values (?, ?)');

        // urutan harus sesuai dengan values
        $query->execute([$id, $countryName]);

        $lastId = $db->lastInsertId();

        $response->getBody()->write(json_encode(
            [
                'message' => 'country disimpan dengan id ' . $lastId
            ]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    // put data
    $app->put('/countries/{id}', function (Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();

        $currentId = $args['id'];
        $countryName = $parsedBody["name"];
        $db = $this->get(PDO::class);

        $query = $db->prepare('UPDATE countries SET name = ? WHERE id = ?');
        $query->execute([$countryName, $currentId]);

        $response->getBody()->write(json_encode(
            [
                'message' => 'country dengan id ' . $currentId . ' telah diupdate dengan nama ' . $countryName
            ]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    // delete data
    $app->delete('/countries/{id}', function (Request $request, Response $response, $args) {
        $currentId = $args['id'];
        $db = $this->get(PDO::class);

        try {
            $query = $db->prepare('DELETE FROM countries WHERE id = ?');
            $query->execute([$currentId]);

            if ($query->rowCount() === 0) {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(
                    [
                        'message' => 'Data tidak ditemukan'
                    ]
                ));
            } else {
                $response->getBody()->write(json_encode(
                    [
                        'message' => 'country dengan id ' . $currentId . ' dihapus dari database'
                    ]
                ));
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(
                [
                    'message' => 'Database error ' . $e->getMessage()
                ]
            ));
        }

        return $response->withHeader("Content-Type", "application/json");
    });
};
