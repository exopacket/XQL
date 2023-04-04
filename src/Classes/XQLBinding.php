<?php

namespace App\XQL\Classes;

use App\XQL\Classes\DB\DBX;
use App\XQL\Classes\Types\XQLBindingType;

class XQLBinding extends XQLObject
{

    protected string $bindFrom;
    protected array $references;
    protected XQLBindingType $bindType;

    public function __construct(string $name, string $from, array $references, XQLBindingType $type)
    {
        $this->name = $name;
        $this->bindFrom = $from;
        $this->references = $references;
        $this->bindType = $type;
    }

    public static function store(string $name, string|XQLObject $from, array $references): XQLBinding
    {
        if ($from instanceof XQLObject) {
            $type = XQLBindingType::FILE_TO_DB;
            $fromName = $from->name;
        } else {
            $type = XQLBindingType::DB_TO_FILE;
            $fromName = $from;
        }
        DBX::relationship();
        return new XQLBinding($name, $fromName, $references, $type);
    }

}