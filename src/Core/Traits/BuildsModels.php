<?php

namespace XQL\Core\Traits;

use XQL\Core\Types\XQLHookType;
use XQL\Core\XQLAttribute;
use XQL\Core\XQLBinding;
use XQL\Core\XQLField;
use XQL\Core\XQLHook;
use XQL\Core\XQLObject;
use XQL\Core\XQLModel;
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
    protected function attach(string $model): XQLObject
    {
        $object = new $model();
        $this->objects[] = $object;
        $this->attached[] = [
            'model_classpath' => $model,
            'model_tree_path' => $this->className(get_called_class())
        ];
        return $object;
    }

//    //"plant" an already created "tree"
//    protected function plant(XQLObject $object): XQLObject
//    {
//        $this->objects[] = $object;
//        return $object;
//    }

    //define a new "tree" object
    protected function group(string $name): XQLObject
    {
        $object = new XQLObject($name);
        $this->objects[] = $object;
        return $object;
    }

    protected function static() : void
    {
        $this->static = true;
    }

    protected function final(): void
    {
        $this->final = true;
    }

}