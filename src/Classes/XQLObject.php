<?php

namespace App\XQL\Classes;

use App\XQL\Classes\Traits\BuildsSchemas;

class XQLObject
{
    use BuildsSchemas;

    protected array $objects;
    protected array $labels;
    protected string $name;

    public function __construct($name = null)
    {
        if(get_class($this) != "XQLObject") $this->name = get_class($this);
        else $this->name = $name;
    }

    public function name(): string {
        return $this->name;
    }

    public function labels(): array {
        return $this->labels;
    }

    public function children(): array {
        return $this->objects;
    }

}