<?php

namespace XQL\Core\Supporting;

use Exception;
use XQL\Cloud\Cloud;
use XQL\Core\Types\XQLBindingType;
use XQL\Core\Utils\DynamicArr;
use XQL\Core\XQLBinding;
use XQL\Core\XQLField;
use XQL\Core\XQLModel;
use XQL\Core\XQLObject;
use XQL\DB\DBX;

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
        $instance = new $class();
        $instance->xpath($instance->modelKey());
        self::construct($instance, $instance, $values, $instance->modelKey());
        if(!$instance->isStatic() && !$instance->isFinal()) {
            $path = $instance->modelKey(true) . "/" . $instance->id() . ".xml";
            Cloud::put($path, $instance->export());
        }
        if(!$instance->isFinal()) {
            DBX::instanceCreated($instance);
        }
        return $instance;
    }

    private static function construct(XQLModel $instance, XQLObject $object, array $values, string $xpath, $dataObject = null) {

        if(!isset($dataObject)) $dataObject = $instance;

        $dArr = new DynamicArr($values);

        if($object->isSearchable()) DBX::updateSearchableFields($instance, $object);
        if($instance->modelKey() !== $xpath) $xpath = $object->xpathFromParent($xpath);

        $i = 0;
        foreach($object->children() as $child) {

            if(is_array($child)) $child = array_values($child)[0];
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
                if ($dArr->exists($child->name())) {
                    $dKey = $dArr->find($child->name());
                    $dataObject->{$child->name()} = (object)[];
                    $child->retrieve($instance, $child, $values[$dKey]);
                    self::construct($instance, $child, $values[$dKey], $xpath, $dataObject->{$child->name()});
                } else if($child->getBindType() === XQLBindingType::FUNCTION) {
                    $child->retrieve($instance, $child, []);
                } else if ($child->isEnforced()) {
                    throw new Exception($child->name() . " binding values are required.");
                }
            } else if($child instanceof XQLModel) {
                if($dArr->exists($child->name())) {
                    $dKey = $dArr->find($child->name());
                    if($child->isMultiple() && is_array($values[$dKey])) {
//                        $dataObject->{$child->name()} = [];
                        $container = new XQLObject($child->groupName(), true);
                        foreach($values[$dKey] as $value) {
                            $model = $child::create($value);
                            $container->appendChild($model);
                            //self::construct($instance, $model, $value, $xpath);
                        }
                        $object->replace($i, $container);
                    } else {
//                        $dataObject->{$child->name()} = (object)[];
                          $object->replace($i, $child::create($values[$dKey]));
                          //self::construct($instance, $child::create($values[$dKey]), $values[$dKey], $xpath);
                    }
                } else if($child->isEnforced()) {
                    throw new Exception($child->name() . " array values are required.");
                }
            } else {
                if($dArr->exists($child->name())) {
                    $dKey = $dArr->find($child->name());
                    if($child->isMultiple() && is_array($values[$dKey])) {
//                        $dataObject->{$child->name()} = [];
                        foreach($values[$dKey] as $value) {
                            self::construct($instance, $child, $value, $xpath);
                        }
                    } else {
//                        $dataObject->{$child->name()} = (object)[];
                        self::construct($instance, $child, $values[$dKey], $xpath);
                    }
                } else if($child->isEnforced()) {
                    throw new Exception($child->name() . " array values are required.");
                }
            }

            $i++;

        }

    }

    public function update(): void
    {

    }

}