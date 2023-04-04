<?php

namespace App\XQL\Classes\DB;

use PDO;

class DBX
{
    private PDO $PDO;
    private bool $con = false;

    public function bind()
    {
        
    }
    
    private static function connect() {
        if(!self::$con) {
            //connect
        }
    }
    
}