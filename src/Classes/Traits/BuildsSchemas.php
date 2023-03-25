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
    protected function bind(string|XQLObject $from, string $reference, string $name = null): XQLBinding
    {
        $name = (!isset($name) && $from instanceof XQLObject) ? get_class($from) : $name ?? $from;
        return new XQLBinding($name, $from, $reference);
    }

    //create new basic field <field>value</field>
    protected function field(string $name): XQLField
    {
        return new XQLField(null, $name);
    }

    //generate field value based on a callback
    protected function generate($name, callable $value): XQLField
    {
        return new XQLField($value(), $name);
    }

    protected function dynamic(string $name, array $params, XQLObject $from, callable $callable): XQLField
    {
        $args = $params; //TODO get all parameters from `$from` with names in `$params` as arguments for the callable
        $v = $callable($args);
        return new XQLField($v, $name);
    }

    //new "branch" in the forrest full of "trees"
    protected function &branch(string $name): XQLObject
    {
        $object = new XQLObject($name);
        $this->objects[] = $object;
        return $object;
    }

    //new global attribute on absolutely anything with optional binding
    protected function &label(string|callable $value, string $name, string|XQLObject $from = null, string $reference = null): XQLObject
    {
        $attribute = new XQLAttribute();
        $this->labels[] = $attribute;
        return $attribute;
    }

}