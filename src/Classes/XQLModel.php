<?php

namespace App\XQL\Classes;

use App\XQL\Classes\Traits\BuildsModels;
use App\XQL\Classes\Traits\BuildsQueries;
use App\XQL\Classes\Traits\GeneratesXML;
use App\XQL\Classes\Types\XQLNamingConvention;

abstract class XQLModel extends XQLObject {

    use BuildsQueries, BuildsModels, GeneratesXML;

    protected string $id;

    protected array $trees = [];
    protected array $hooks = [];

    public function __construct(array $data = null) {
        $this->build();
        if(isset($data)) $this->populate($data);
        parent::__construct();
    }

    abstract protected function schema(XQLModel $model);

    protected function build()
    {
        $this->schema($this);
    }

    protected function populate(array $data) {
        if(array_key_exists("id", $data) && isset($data['id'])) $this->id = $data['id'];
        else $this->generateId();
    }

    protected function export(): string
    {
        $string = $this->xml(true);
        return $string;
    }

    public function children(): array
    {
        return array_merge($this->trees, $this->objects);
    }

    public function id(): string
    {
        if(!isset($this->id)) $this->generateId();
        return $this->id;
    }

    protected function binded(): XQLObject
    {
        return $this;
    }

    protected function generateId(): void
    {
        $data = get_called_class() . ":" . time() . ":" . microtime();
        $this->id = hash("sha1", $data);
    }

    public function modelKey(bool $plural = false, bool $camelCase = false)
    {
        $cases = $this->cases();
        $arr = ($plural) ? $cases['plural'] : $cases['singular'];
        return ($camelCase) ? $arr['camel'] : $arr['snake'];
    }

}