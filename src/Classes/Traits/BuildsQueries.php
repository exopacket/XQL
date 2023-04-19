<?php

namespace App\XQL\Classes\Traits;

use App\XQL\Classes\DB\DBX;
use App\XQL\Classes\Utils\DynamicArr;
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
        $instance = new $class(['id' => null]);
        self::iterate($instance, $instance, $values);
        $path = $instance->modelKey(true) . "/" . $instance->id() . ".xml";
        Cloud::put($path, $instance->export());
        DBX::instanceCreated($instance);
        return $instance;
    }

    private static function iterate(XQLModel $instance, XQLObject $object, array $values, $dataObject = null) {

        if(!isset($dataObject)) $dataObject = $instance;

        $dArr = new DynamicArr($values);

        foreach($object->children() as $child) {

            if($child instanceof XQLField) {
                if($dArr->exists($child->name())) {
                    $dKey = $dArr->find($child->name());
                    if($child->isMultiple() && is_array($values[$dKey])) {
                        foreach($values[$dKey] as $value) $child->appendMultiple($value);
                        $dataObject->{$child->name()} = $values[$dKey];
                    } else {
                        $dataObject->{$child->name()} = $values[$dKey];
                        $child->value($values[$dKey]);
                    }
                } else if($child->isEnforced()) {
                    throw new Exception($child->name() . " is required.");
                }
            } else if($child instanceof XQLBinding) {
                if($dArr->exists($child->name())) {
                    $dKey = $dArr->find($child->name());
                    $dataObject->{$child->name()} = (object)[];
                    $child->retrieve($values[$dKey]);
                    self::iterate($instance, $child, $values[$dKey], $dataObject->{$child->name()});
                } else if($child->isEnforced()) {
                    throw new Exception($child->name() . " binding values are required.");
                }
            } else {
                if($dArr->exists($child->name())) {
                    $dKey = $dArr->find($child->name());
                    $dataObject->{$child->name()} = (object)[];
                    self::iterate($instance, $child, $values[$dKey], $dataObject->{$child->name()});
                }
            }

        }

    }

    public function update(): void
    {

    }
}