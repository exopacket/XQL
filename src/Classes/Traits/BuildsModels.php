<?php

namespace App\XQL\Classes\Traits;

use App\XQL\Classes\XQLAttribute;
use App\XQL\Classes\XQLBinding;
use App\XQL\Classes\XQLField;
use App\XQL\Classes\XQLObject;
use App\XQL\Classes\XQLModel;

trait BuildsModels
{

    //attach another model
    protected function &attach(XQLModel $model): XQLObject
    {
        $this->trees[] = $model;
        return $model;
    }

    //"plant" an already created "tree"
    protected function &plant(XQLObject $object, string $name = null): XQLObject
    {
        $name = (isset($name)) ? $name : get_class($object);
        $this->trees[] = [ $name => $object ];
        return $object;
    }

    //define a new "tree" object
    protected function &root(string $name): XQLObject
    {
        $object = new XQLObject($name);
        $this->trees[] = $object;
        return $object;
    }
}