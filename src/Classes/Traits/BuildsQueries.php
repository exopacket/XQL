<?php

namespace App\XQL\Classes\Traits;

use App\XQL\Classes\DB\DBX;
use App\XQL\Classes\Utils\Env;
use App\XQL\Classes\XQLBinding;
use App\XQL\Classes\XQLField;
use App\XQL\Classes\XQLModel;
use App\XQL\Classes\XQLObject;
use App\XQL\Cloud\Cloud;
use Exception;
use PDO;

trait BuildsQueries
{
    public static function where(): void
    {

    }

    public static function create(array $values): XQLModel
    {
        $class = get_called_class();
        $instance = new $class();
        self::iterate($instance, $instance, $values);
        //Cloud::put("tests/test2", $instance->export());
        return $instance;
    }

    private static function iterate(XQLModel $instance, XQLObject $object, array $values, $dataObject = null) {

        if(!isset($dataObject)) $dataObject = $instance;

        foreach($object->children() as $child) {

            if($child instanceof XQLField) {
                if(array_key_exists($child->name(), $values)) {
                    if($child->isMultiple() && is_array($values[$child->name()])) {
                        foreach($values[$child->name()] as $value) $child->appendMultiple($value);
                        $dataObject->{$child->name()} = $values[$child->name()];
                    } else {
                        $dataObject->{$child->name()} = $values[$child->name()];
                        $child->value($values[$child->name()]);
                    }
                } else if($child->isEnforced()) {
                    throw new Exception($child->name() . " is required.");
                }
            } else if($child instanceof XQLBinding) {
                if(array_key_exists($child->name(), $values)) {
                    $child->retrieve($values[$child->name()]);
                    $dataObject->{$child->name()} = (object)[];
                    self::iterate($instance, $child, $values[$child->name()], $dataObject->{$child->name()});
                } else if($child->isEnforced()) {
                    throw new Exception($child->name() . " binding values are required.");
                }
            } else {
                if(array_key_exists($child->name(), $values)) {
                    $dataObject->{$child->name()} = (object)[];
                    self::iterate($instance, $child, $values[$child->name()], $dataObject->{$child->name()});
                }
            }

        }

    }

    public function _create(array $values): XQLModel
    {
        $keys = array_keys($values);
        foreach($keys as $key) {
            $this->{$key} = $values[$key];
            foreach($this->children() as $child) {
                if($child instanceof XQLField && $child->name() === $key) {
                    $child->value($values[$key]);
                }
            }
        }
        //Cloud::put("tests/test", $this->export());
        return $this;
    }

    public function update(): void
    {

    }
}