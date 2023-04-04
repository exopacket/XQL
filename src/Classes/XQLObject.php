<?php

namespace App\XQL\Classes;

use App\XQL\Classes\Traits\BuildsSchemas;

class XQLObject
{
    use BuildsSchemas;

    protected array $objects;
    protected array $labels;
    protected string $name;
    protected bool $cached = false;
    protected bool $multiple = false;
    protected bool $enforced = false;

    public function __construct($name = null)
    {
        $basename = $name ?? (new \ReflectionClass($this))->getShortName();
        if($basename != "XQLObject") $name = $basename;
        $this->name = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $name)), '_');
    }

    public function name(): string {
        return $this->name ?? [];
    }

    public function labels(): array {
        return $this->labels ?? [];
    }

    public function children(): array {
        return $this->objects ?? [];
    }

    public function attributes(): array
    {
        return [];
    }
}