<?php

namespace App\XQL\Classes\Traits;

use App\XQL\Classes\Types\XQLHookType;
use App\XQL\Classes\XQLAttribute;
use App\XQL\Classes\XQLBinding;
use App\XQL\Classes\XQLField;
use App\XQL\Classes\XQLHook;
use App\XQL\Classes\XQLObject;
use App\XQL\Classes\XQLModel;
use Dompdf\Exception;

trait BuildsModels
{

    protected function hook(XQLHookType $type, string $table, array $columns = []): XQLHook
    {
        $hook = new XQLHook($type, $table, $columns);
        foreach($this->hooks as $item) {
            if($item->type() === $type) {
                throw new Exception("Only one hook of the same type is allowed to be defined in a model.");
            }
        }
        $this->hooks[] = $hook;
        return $hook;
    }

    //attach another model
    protected function attach($model): XQLObject
    {
        $object = new $model();
        $this->trees[] = $object;
        return $object;
    }

    //"plant" an already created "tree"
    protected function plant(XQLObject $object, string $name = null): XQLObject
    {
        $name = (isset($name)) ? $name : $object->name();
        $this->trees[] = [ $name => $object ];
        return $object;
    }

    //define a new "tree" object
    protected function root(string $name): XQLObject
    {
        $object = new XQLObject($name);
        $this->trees[] = $object;
        return $object;
    }
}