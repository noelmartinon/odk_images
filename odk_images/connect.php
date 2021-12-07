<?php
/*
 * Description: Display image from blob stores in ODK Aggregate and ODK Central databases
 * Version: 1.0.0
 * Author: Noël MARTINON
 * Licence: GPLv3
 */

require_once 'config.php';

function pgsql_connect(string $host, string $port, string $db, string $user, string $password): PDO
{
    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
        return new PDO(
            $dsn,
            $user,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    } catch (PDOException $e) {
        die(""); //~ die($e->getMessage());
    }
}

$pdo_array = array();
if (isset($aggregate_host))
    $pdo_array['aggregate'] = pgsql_connect($aggregate_host, $aggregate_port, $aggregate_db, $aggregate_user, $aggregate_password);
if (isset($central_host))
    $pdo_array['central'] = pgsql_connect($central_host, $central_port, $central_db, $central_user, $central_password);

return $pdo_array;
