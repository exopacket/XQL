<?php

namespace App\XQL\Classes\DB;

use App\XQL\Classes\Utils\Env;
use App\XQL\Classes\XQLBindingClause;
use Exception;
use PDO;

class DBX
{
    private static PDO $data;
    private static PDO $xql;

    public function bind()
    {
        
    }

    public static function getBindedValues(string $table, array|string $columns, XQLBindingClause $where, array $equals) {

        $conditions = $where->get();

        self::connect();
        $con = self::$data;
        $query = "SELECT";
        if(!is_array($columns)) {
            $query .= " " . $columns;
        } else {
            $first = true;
            foreach($columns as $column) {
                $query .= ((!$first) ? ", " : " ") . $column;
                $first = false;
            }
        }

        $query .= " FROM `" . $table . "` WHERE";

        $res = self::parseWhere($query, $conditions, $equals);
        $query = $res[0];
        $toBind = $res[1];

        $stmt = $con->prepare($query);
        $order = 1;
        for($i=0; $i<count($toBind); $i++) {
            $condition = $toBind[$i];
            if(is_array($condition['value'])) {
                foreach($condition['value'] as $value) {
                    $stmt->bindValue($order, $value);
                    $order++;
                }
            } else {
                $stmt->bindValue($order, $condition['value']);
                $order++;
            }
        }
        $stmt->execute();

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }

    private static function parseWhere(string $query, array $conditions, array $equals, array $values = []): array
    {
        if(count($conditions) == 0) return [$query . " 1", $values];
        foreach($conditions as $condition) {
            if(array_key_exists("condition", $condition)) {
                $query .= " " . $condition['condition'];
            } else if(array_key_exists("column", $condition)) {
                if(!array_key_exists($condition['key'], $equals)) {
                    throw new Exception("Missing value for " . $condition['key'] . " (the column `" . $condition['column'] . "`).");
                }
                if(is_array($equals[$condition['key']])) {
                    $condition['value'] = $equals[$condition['key']];
                    $values[] = $condition;
                    $query .= " `" . $condition['column'] . "` IN (";
                    $first = true;
                    foreach($equals[$condition['key']] as $ignored) {
                        $query .= ($first) ? "?" : ", ?";
                        $first = false;
                    }
                    $query .= ")";
                } else {
                    $condition['value'] = $equals[$condition['key']];
                    $values[] = $condition;
                    $query .= " `" . $condition['column'] . "` = ?";
                }
            } else if(is_array($condition)) {
                $res = self::parseWhere($query, $condition, $equals, $values);
                $query = $res[0];
                $values = $res[1];
            }
        }
        return [$query, $values];
    }
    
    private static function connect()
    {
        if(!isset(self::$data) || !isset(self::$xql)) {
            $xqlDriver = Env::get("XQL_DB_DRIVER");
            if($xqlDriver == "mysql") {
                self::$xql = self::mysql(
                    Env::get("XQL_DB_HOST"),
                    Env::get("XQL_DB_PORT"),
                    Env::get("XQL_DB_DATABASE"),
                    Env::get("XQL_DB_USERNAME"),
                    Env::get("XQL_DB_PASSWORD"));
            }

            $dataDriver = Env::get("XQL_BINDED_DB_DRIVER");
            if($dataDriver == "mysql") {
                self::$data = self::mysql(
                    Env::get("XQL_BINDED_DB_HOST"),
                    Env::get("XQL_BINDED_DB_PORT"),
                    Env::get("XQL_BINDED_DB_DATABASE"),
                    Env::get("XQL_BINDED_DB_USERNAME"),
                    Env::get("XQL_BINDED_DB_PASSWORD"));
            }
        }
    }

    private static function mysql($host, $port, $database, $username, $password): PDO
    {
        $con = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $con;
    }
    
}