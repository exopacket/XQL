<?php

namespace App\XQL\Classes\DB;

use App\XQL\Classes\Utils\DynamicValue;
use App\XQL\Classes\Utils\Env;
use App\XQL\Classes\XQLBindingClause;
use App\XQL\Classes\XQLField;
use App\XQL\Classes\XQLModel;
use App\XQL\Classes\XQLObject;
use Exception;
use PDO;

class DBX
{
    protected static PDO $data;
    protected static PDO $xql;

    protected static array $searchables = [];

    public static function instanceCreated(XQLModel $instance)
    {
        self::connect();
        self::insertInstance($instance);
        self::insertSearchables($instance);
        self::insertBindings($instance);
        self::insertHooks($instance);
        self::createHookTriggers($instance);
        self::createBindingTriggers($instance);
    }

    protected static function insertInstance(XQLModel $instance) {
        $con = self::$xql;
        $query = "INSERT INTO instances VALUES(?, ?, ?)";
        $stmt = $con->prepare($query);
        $stmt->bindValue(1, $instance->id());
        $stmt->bindValue(2, $instance->modelKey());
        $stmt->bindValue(3, $instance->modelKey(true));

        $stmt->execute();
    }

    protected static function insertSearchables(XQLModel $instance) {

    }

    protected static function insertBindings(XQLModel $instance) {

    }

    protected static function insertHooks(XQLModel $instance) {

    }

    protected static function createHookTriggers(XQLModel $instance) {

    }

    protected static function createBindingTriggers(XQLModel $instance) {

    }

    protected static function createTable(PDO $con, string $tableName, array $config)
    {
        $query = "CREATE TABLE IF NOT EXISTS " . $tableName . "( ";
        $auto = !array_key_exists("primary", $config) && !array_key_exists("id", $config['columns']);
        if($auto) {
            $query .= "`id` int ";
        }
        $columns = array_keys($config['columns']);
        for($i=0; $i<count($columns); $i++) {
            if($i > 0 || $auto) $query .= ", ";
            $columnName = $columns[$i];
            $column = $config['columns'][$columnName];
            $type = $column['type'];
            $null = !isset($column['null']) || !(($column['null'] === 'not' || $column['null'] === 'no'));
            $query .= "`" . $columnName . "` " . $type . (($null) ? '' : ' not') . ' null ';
        }
        if($auto) {
            $query .= ", primary key (`id`) ";
        } else {
            $query .= ", primary key (`" . $config['primary'] . "`)";
        }
        $query .= " )";
        $con->exec($query);

        if($auto) {
            $con->exec("alter table `" . $tableName . "` modify `id` int auto_increment");
        }
    }

    protected static function getSearchableFields(XQLModel $model)
    {
        if(isset(self::$searchables['$model'])) return;
        self::connect();
        $con = self::$xql;
        $query = "SELECT id, field_name FROM models_with_searchable WHERE model_name=?";
        $stmt = $con->prepare($query);
        $stmt->bindValue(1, $model->modelKey());
        $stmt->execute();

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $arr = $stmt->fetchAll();
        if(count($arr) > 0) self::$searchables = array_merge(array($model->modelKey() => $arr), self::$searchables);
    }

    public static function updateSearchableFields(XQLModel $instance, XQLObject $field)
    {
        self::getSearchableFields($instance);
        if(isset(self::$searchables[$instance->modelKey()])) {
            $searchables = array_column(self::$searchables[$instance->modelKey()],  "field_name");
            if(in_array($field->fieldName(), $searchables)) return;
            self::connect();
            $con = self::$xql;
            $insertQuery = "INSERT INTO models_with_searchable(`model_name`, `field_name`) VALUES(?, ?)";
            $stmt = $con->prepare($insertQuery);
            $stmt->bindValue(1, $instance->modelKey());
            $stmt->bindValue(2, $field->fieldName());
            $stmt->execute();
            self::$searchables[$instance->modelKey()][] = $field->fieldName();
        } else {
            self::connect();
            $con = self::$xql;
            $insertQuery = "INSERT INTO models_with_searchable(`model_name`, `field_name`) VALUES(?, ?)";
            $stmt = $con->prepare($insertQuery);
            $stmt->bindValue(1, $instance->modelKey());
            $stmt->bindValue(2, $field->fieldName());
            $stmt->execute();
            $tableName = $instance->modelKey(true) . "_searchable";
            self::createTable($con, $tableName, [
                'columns' => [
                    'instance_id' => [
                        'type' => 'varchar(255)'
                    ],
                    'xpath' => [
                        'type' => 'varchar(255)'
                    ],
                    'field_name' => [
                        'type' => 'varchar(255)'
                    ],
                    'field_type' => [
                        'type' => 'varchar(255)'
                    ],
                    'string_value' => [
                        'type' => 'text'
                    ],
                    'integer_value' => [
                        'type' => 'int'
                    ],
                    'float_value' => [
                        'type' => 'float'
                    ],
                    'datetime_value' => [
                        'type' => 'timestamp'
                    ]
                ]
            ]);
        }
    }

    public static function insertSearchableValue(XQLModel $instance, XQLField $field, $value = null)
    {
        if(!isset($value)) $value = $field->value();
        $instance_id = $instance->id();
        $xpath = $field->xpath();
        $field_name = $field->fieldName();
        $field_type = $field->type();
        $dynamic_values = (new DynamicValue($value))->all();

        self::connect();
        $con = self::$xql;

        $tableName = $instance->modelKey(true) . "_searchable";

        $query = "INSERT INTO `" . $tableName .
            "` (`instance_id`, `xpath`, `field_name`, `field_type`, `string_value`, `integer_value`, `float_value`, `datetime_value`) VALUES(?,?,?,?,?,?,?,?)";

        $stmt = $con->prepare($query);
        $stmt->bindValue(1, $instance_id);
        $stmt->bindValue(2, $xpath);
        $stmt->bindValue(3, $field_name);
        $stmt->bindValue(4, $field_type->value);
        $stmt->bindValue(5, $dynamic_values['string']);
        $stmt->bindValue(6, $dynamic_values['integer']);
        $stmt->bindValue(7, $dynamic_values['float']);
        $stmt->bindValue(8, $dynamic_values['timestamp']);

        $stmt->execute();

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

    protected static function parseWhere(string $query, array $conditions, array $equals, array $values = []): array
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
    
    protected static function connect()
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

    protected static function mysql($host, $port, $database, $username, $password): PDO
    {
        $con = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $con;
    }
    
}