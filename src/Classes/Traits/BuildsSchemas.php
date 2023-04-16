<?php

namespace App\XQL\Classes\Traits;

use App\XQL\Classes\XQLAttribute;
use App\XQL\Classes\XQLBinding;
use App\XQL\Classes\XQLField;
use App\XQL\Classes\XQLObject;
use App\XQL\Classes\XQLModel;

trait BuildsSchemas
{

    //bind an XML file to DB or vice versa
    protected function bind(string|XQLObject $from, string|array $reference, string $name = null): XQLBinding
    {
        $name = (!isset($name) && $from instanceof XQLObject) ? get_class($from) : $name ?? $from;
        $references = [];
        if(is_array($reference)) $references = $reference;
        else $references = [$reference];
        return XQLBinding::store($name, $from, $references);
    }

    //bind all DB fields to XML
    protected function bindAll(string|XQLObject $from, string $name = null): XQLBinding
    {
        $name = (!isset($name) && $from instanceof XQLObject) ? get_class($from) : $name ?? $from;
        return XQLBinding::store($name, $from, "*");
    }

    //create new basic field <field>value</field>
    protected function field(string $name): XQLField
    {
        $object = new XQLField(null, $name);
        $this->objects[] = $object;
        return $object;
    }

    //generate field value based on an anonymous functions
    protected function generate($name, callable $value): XQLField
    {
        $object = new XQLField($value(), $name);
        $this->objects[] = $object;
        return $object;
    }

    //generate field value based on an analytic or use case specific function's return value
    protected function dynamic(string $name, array $params, XQLObject $from, callable $callable): XQLField
    {
        $args = $params; //TODO get all parameters from `$from` with names in `$params` as arguments for the callable
        $v = $callable($args);
        return new XQLField($v, $name);
    }

    //new "branch" in the forest full of "trees"
    protected function branch(string $name): XQLObject
    {
        $object = new XQLObject($name);
        $this->objects[] = $object;
        return $object;
    }

    //new global attribute on absolutely anything with optional binding
//    protected function label(string|callable $value, string $name, string|XQLObject $from = null, string $reference = null): XQLObject
//    {
//        $attribute = new XQLAttribute();
//        $this->labels[] = $attribute;
//        return $attribute;
//    }

    protected function cache()
    {
        $this->cached = true;
        return $this;
    }

    protected function multiple(string $name = null) {
        $this->multiple = true;
        if(isset($name)) $this->name = $name;
        return $this;
    }

    protected function enforced() {
        $this->enforced = true;
        return $this;
    }

}