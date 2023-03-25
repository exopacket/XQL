<?php

use App\XQL\Classes\Traits\BuildsModels;
use App\XQL\Classes\Traits\BuildsQueries;
use App\XQL\Classes\Traits\GeneratesXML;
use App\XQL\Classes\Types\XQLBindingType;
use App\XQL\Classes\XQLObject;


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
    protected XQLBindingType $outflowType;

    abstract protected function schema($model);

    protected function build()
    {
        $this->schema($this);
    }

    protected function objectify()
    {

    }

    public function export()
    {
        $string = $this->xml($this);
    }

}