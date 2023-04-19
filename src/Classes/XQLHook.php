<?php

namespace App\XQL\Classes;

use App\XQL\Classes\Types\XQLHookType;

class XQLHook
{

    protected XQLHookType $type;
    protected string $table;
    protected array $columns;

    public function __construct(XQLHookType $type, string $table, array $columns)
    {
        $this->type = $type;
        $this->table = $table;
        $this->columns = $columns;
    }

    public function type()
    {
        return $this->type;
    }

}