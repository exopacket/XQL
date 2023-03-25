<?php

namespace App\XQL\Classes\DB;

use App\XQL\Classes\Types\XQLBindingType;
use \SQLite3;

class DBX extends \SQLite3
{
    protected string $filename = "/home/ryan/test-db.sqlite";
    public function __construct(bool $createSchema)
    {
        parent::__construct($this->filename);
        if($createSchema) $this->tables();
    }

    //create relationship
    public static function relationship(string $from, string $to, XQLBindingType $type) {
        $dbx = new DBX(false);
    }

    //get relationships
    public static function match(string $from) {
        $dbx = new DBX(false);
    }

    private function tables() {
        $this->query("CREATE TABLE IF NOT EXISTS `bind_relationships` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `type` INTEGER, `from` VARCHAR(255), `to` VARCHAR(255))");
        $this->query("CREATE TABLE IF NOT EXISTS `objects` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `value` VARCHAR(255))");
        $this->query("CREATE TABLE IF NOT EXISTS `binded` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `bind` INTEGER, `object` INTEGER)");
    }

}