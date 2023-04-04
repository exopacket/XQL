<?php

namespace App\XQL\Classes;

use App\XQL\Classes\Traits\BuildsModels;
use App\XQL\Classes\Traits\BuildsQueries;
use App\XQL\Classes\Types\XQLBindingType;
use App\XQL\Classes\Traits\GeneratesXML;

abstract class XQLModel extends XQLObject {

    use BuildsQueries, BuildsModels, GeneratesXML;

    protected array $trees = [];

    public function __construct() { $this->build(); parent::__construct(); }

    abstract protected function schema(XQLModel $model);

    protected function build()
    {
        $this->schema($this);
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

    protected function binded(): XQLObject
    {
        return $this;
    }

}