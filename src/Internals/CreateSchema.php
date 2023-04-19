<?php

namespace App\XQL\Internals;

use App\XQL\Classes\DB\DBX;

class CreateSchema extends DBX
{

    public static function create()
    {
        self::connect();
        $con = self::$xql;
        self::instances($con);
        self::withSearchable($con);
        self::withBindings($con);
    }

    private static function instances($con) {
        self::createTable($con, "instances", [
            'columns' => [
                'id' => [
                    'type' => 'varchar(40)'
                ],
                'type' => [
                    'type' => 'varchar(255)'
                ],
                'path' => [
                    'type' => 'varchar(255)'
                ]
            ],
            'primary' => 'id'
        ]);
    }

    private static function withSearchable($con) {
        self::createTable($con, "models_with_searchable", [
            'columns' => [
                'model_name' => [
                    'type' => 'varchar(255)'
                ],
                'field_name' => [
                    'type' => 'varchar(255)'
                ],
            ],
        ]);
    }

    private static function withBindings($con) {
        self::createTable($con, "models_with_bindings", [
            'columns' => [
                'model_name' => [
                    'type' => 'varchar(255)'
                ],
                'table_name' => [
                    'type' => 'varchar(255)'
                ],
                'column_name' => [
                    'type' => 'varchar(255)'
                ],
                'field_name' => [
                    'type' => 'varchar(255)'
                ],
            ],
        ]);
    }

}