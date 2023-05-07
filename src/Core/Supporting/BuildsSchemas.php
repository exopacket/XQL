<?php

namespace XQL\Core\Supporting;

use XQL\Core\Types\XQLDataType;
use XQL\Core\XQLAttribute;
use XQL\Core\XQLBinding;
use XQL\Core\XQLField;
use XQL\Core\XQLObject;
use XQL\Core\XQLModel;

trait BuildsSchemas
{

    //bind an XML file to DB or vice versa
    protected function bindTable(string|XQLModel $from, string|array $reference, string $name = null): XQLBinding
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
    protected function bindAll(string|XQLModel $from, string $name = null): XQLBinding
    {
        $name = (!isset($name) && $from instanceof XQLObject) ? $this->className($from) : $name ?? $this->toClass($from);
        $object = XQLBinding::store($name, $from, []);
        $this->objects[] = $object;
        return $object;
    }

    protected function bindThis(string $classpath, string $name = null): XQLBinding
    {
        $name = (!isset($name)) ? $this->className($classpath) : $name ?? $this->toClass($classpath);
        $object = XQLBinding::store($name, $classpath, [], $this);
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
    protected function generate($name, $fn): XQLBinding
    {
        $object = XQLBinding::await($name, $this, [], $fn);
        $this->objects[] = $object;
        return $object;
    }

    //generate field value based on an analytic or use case specific function's return value
    protected function dynamic(string $name, array $params, XQLObject $from, $fn): XQLBinding
    {
        $object = XQLBinding::await($name, $from, $params, $fn);
        $this->objects[] = $object;
        return $object;
    }

    protected function defined(string $name, string $fn): XQLBinding
    {
        $object = XQLBinding::await($name, $this, [], $fn);
        $this->objects[] = $object;
        return $object;
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

    protected function primary(string $child_name)
    {
        if($this instanceof XQLField) return $this;
        return [
            "field" => $child_name,
            "object" => $this
        ];
    }

}