<?php

namespace App\XQL\Classes\Traits;

use App\XQL\Classes\Types\XQLDataType;
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
        $name = (!isset($name) && $from instanceof XQLObject) ? $this->className($from) : $name ?? $this->toClass($from);
        $references = [];
        if(is_array($reference)) $references = $reference;
        else $references = [$reference];
        $object = XQLBinding::store($name, $from, $references);
        $this->objects[] = $object;
        return $object;
    }

    //bind all DB fields to XML
    protected function bindAll(string|XQLObject $from, string $name = null): XQLBinding
    {
        $name = (!isset($name) && $from instanceof XQLObject) ? $this->className($from) : $name ?? $this->toClass($from);
        $object = XQLBinding::store($name, $from, []);
        $this->objects[] = $object;
        return $object;
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

    protected function searchable()
    {
        $this->searchable = true;
        return $this;
    }

    protected function multiple() {
        $this->multiple = true;
        return $this;
    }

    protected function enforced() {
        $this->enforced = true;
        return $this;
    }

}