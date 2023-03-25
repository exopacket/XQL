<?php

namespace App\XQL\Classes;

use App\XQL\Classes\Traits\BuildsModels;
use App\XQL\Classes\Traits\BuildsQueries;
use App\XQL\Classes\Types\XQLBindingType;
use App\XQL\Classes\Traits\GeneratesXML;

abstract class XQLModel extends XQLObject {

    use BuildsQueries, BuildsModels, GeneratesXML;

    /*
     *
     *   Person
     *      Full Name [nickname]
     *      Gender [preferred pronouns]
     *      Favorite Colors
     *          red [shade]
     *          blue [shade]
     *
     *  =======================================================
     *
     *  <person>
     *      <full_name>Ryan Fitzgerald</full_name>
     *      <gender pronouns="...">male</gender>
     *      <favorite_colors>
     *          <color shade="#f00">red</color>
     *          <color shade="#00f">blue</color>
     *      </favorite_colors>
     *  </person>
     * 
     */

    protected array $trees;
    protected XQLBindingType $bindingType;

    public function __construct() { $this->build(); parent::__construct(); }

    abstract protected function schema(XQLModel $model);

    protected function build()
    {
        $this->schema($this);
    }

    protected function objectify()
    {

    }

    public function export()
    {
        $string = $this->xml($this, true);
        echo $string;
    }

    public function children(): array
    {
        return $this->trees ?? [];
    }

    protected function binded(): XQLObject
    {
        return $this;
    }

}