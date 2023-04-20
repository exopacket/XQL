<?php

namespace XQL\Core\Traits;

use XQL\Cloud\Cloud;
use XQL\Core\Utils\DynamicArr;
use XQL\Core\XQLBinding;
use XQL\Core\XQLField;
use XQL\Core\XQLModel;
use XQL\Core\XQLObject;
use XQL\DB\DBX;
use Exception;

trait BuildsQueries
{
    public static function where(): void
    {

    }

    public static function fetch(string $id)
    {
        $class = get_called_class();
        $instance = new $class(['id' => $id]);
        return $instance;
    }

    public static function create(array $values): XQLModel
    {
        $class = get_called_class();
        $instance = new $class(['id' => null]);
        $instance->xpath($instance->modelKey());
        self::iterate($instance, $instance, $values, $instance->modelKey());
        $path = $instance->modelKey(true) . "/" . $instance->id() . ".xml";
        Cloud::put($path, $instance->export());
        DBX::instanceCreated($instance);
        return $instance;
    }

    private static function iterate(XQLModel $instance, XQLObject $object, array $values, string $xpath, $dataObject = null) {

        if(!isset($dataObject)) $dataObject = $instance;

        $dArr = new DynamicArr($values);

        if($object->isSearchable()) DBX::updateSearchableFields($instance, $object);
        if($instance->modelKey() !== $xpath) $xpath = $object->xpathFromParent($xpath);

        foreach($object->children() as $child) {

            if($child->isSearchable()) DBX::updateSearchableFields($instance, $child);
            $child->xpathFromParent($xpath);

            if($child instanceof XQLField) {
                if($dArr->exists($child->name())) {
                    $dKey = $dArr->find($child->name());
                    if($child->isMultiple() && is_array($values[$dKey])) {
                        foreach($values[$dKey] as $value) {
                            $child->appendMultiple($value);
                            if($child->isSearchable()) DBX::insertSearchableValue($instance, $child, $value);
                        }
                        $dataObject->{$child->name()} = $values[$dKey];
                    } else {
                        $dataObject->{$child->name()} = $values[$dKey];
                        $child->value($values[$dKey]);
                        if($child->isSearchable()) DBX::insertSearchableValue($instance, $child);
                    }
                } else if($child->isEnforced()) {
                    throw new Exception($child->name() . " is required.");
                }
            } else if($child instanceof XQLBinding) {
                if($dArr->exists($child->name())) {
                    $dKey = $dArr->find($child->name());
                    $dataObject->{$child->name()} = (object)[];
                    $child->retrieve($instance, $child, $values[$dKey]);
                    self::iterate($instance, $child, $values[$dKey], $xpath, $dataObject->{$child->name()});
                } else if($child->isEnforced()) {
                    throw new Exception($child->name() . " binding values are required.");
                }
            } else {
                if($dArr->exists($child->name())) {
                    $dKey = $dArr->find($child->name());
                    $dataObject->{$child->name()} = (object)[];
                    self::iterate($instance, $child, $values[$dKey], $xpath, $dataObject->{$child->name()});
                }
            }

        }

    }

    public function update(): void
    {

    }
}