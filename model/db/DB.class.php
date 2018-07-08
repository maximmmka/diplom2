<?php
namespace model\db;
require_once 'connection.php';

class DB {

    /**
     * @return \PDO
     */
    public function getDBConnect ()
    {
        try {
            $pdo = new \PDO('mysql:host='.SERVER.';dbname='.DATABASE.';charset=utf8', USER, PASSWORD);
        }
        catch (\Exception $e) {
            throw new \PDOException('Ошибка подключения к базе данных', 0, $e);
        }

        return $pdo;
    }
}