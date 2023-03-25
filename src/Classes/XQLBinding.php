<?php

namespace App\XQL\Classes;

use App\XQL\Classes\DB\DBX;
use App\XQL\Classes\Types\XQLBindingType;

class XQLBinding extends XQLObject
{

    protected string $bindFrom;
    protected string $reference;
    protected string $bindType;

    public function __construct(string $name, string $from, string $reference, XQLBindingType $type)
    {
        $this->name = $name;
        $this->bindFrom = $from;
        $this->reference = $reference;
        $this->bindType = $type;
    }

    public static function store(string $name, string|XQLObject $from, string $reference): XQLBinding
    {
        if ($from instanceof XQLObject) {
            $type = XQLBindingType::FILE_TO_DB;
            $fromName = $from->name;
        } else {
            $type = XQLBindingType::DB_TO_FILE;
            $fromName = $from;
        }
        DBX::relationship($reference, $name, $type);
        return new XQLBinding($name, $fromName, $reference, $type);
    }

}