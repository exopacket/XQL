<?php

namespace XQL\Core;

use XQL\Cloud\Cloud;
use XQL\Core\Traits\BuildsModels;
use XQL\Core\Traits\BuildsQueries;
use XQL\Core\Traits\GeneratesXML;
use XQL\Core\Types\XQLNamingConvention;
use XQL\Core\Utils\DynamicArr;
use SimpleXMLElement;

abstract class XQLModel extends XQLObject {

    use BuildsQueries, BuildsModels, GeneratesXML;

    protected string $id;

    protected array $trees = [];
    protected array $hooks = [];

    public function __construct(array $data = null) {
        $this->build();
        if(isset($data)) $this->populate($data);
        parent::__construct();
    }

    abstract protected function schema(XQLModel $model);

    protected function build()
    {
        $this->schema($this);
    }

    protected function populate(array $data) {
        if(array_key_exists("id", $data) && isset($data['id'])) $this->id = $data['id'];
        else $this->generateId();
        $id = $this->id;
        $this->iterate($this, simplexml_load_string(Cloud::get("results/$id.xml")));
    }

    private function iterate(XQLObject $object, SimpleXMLElement $element, $dataObject = null) {

        if(!isset($dataObject)) $dataObject = $this;

        $values = get_object_vars($element->children());
        $dArrSingle = new DynamicArr($values, "singular");
        $dArrMultiple = new DynamicArr($values, "plural");

        foreach($object->children() as $child) {

            if($child instanceof XQLField) {
                if($child->isMultiple() && $dArrMultiple->exists($child->name())) {
                    $dKey = $dArrMultiple->find($child->name());
                    $multipleValues = $values[$dKey];
                    if($values[$dKey] instanceof SimpleXMLElement) $multipleValues = array_values(get_object_vars($values[$dKey]))[0];
                    if(is_array($multipleValues)) {
                        foreach ($multipleValues as $value) {
                            $child->appendMultiple($value);
                        }
                        $dataObject->{$child->name()} = $multipleValues;
                    }
                } else if($dArrSingle->exists($child->name())) {
                    $dKey = $dArrSingle->find($child->name());
                    $dataObject->{$child->name()} = $values[$dKey];
                    $child->value($values[$dKey]);
                } else if($child->isEnforced()) {
                    throw new Exception($child->name() . " is required.");
                }
            } else if($child instanceof XQLBinding) {
                if(array_key_exists($child->fieldName(), $values)) {
                    $child->parse(get_object_vars($values[$child->fieldName()]), $dataObject);
                }
//                if($dArr->exists($child->name())) {
////                    $dKey = $dArr->find($child->name());
////                    $dataObject->{$child->name()} = (object)[];
////                    $child->retrieve($instance, $child, $values[$dKey]);
////                    self::iterate($instance, $child, $values[$dKey], $xpath, $dataObject->{$child->name()});
//                } else if($child->isEnforced()) {
//                    throw new Exception($child->name() . " binding values are required.");
//                }
            } else {
//                if($dArr->exists($child->name())) {
//                    $dKey = $dArr->find($child->name());
//                    $dataObject->{$child->name()} = (object)[];
//                    $this->iterate($child, $values[$dKey], $dataObject->{$child->name()});
//                }
            }

        }

    }

    protected function export(): string
    {
        $string = $this->xml(true);
        return $string;
    }

    public function children(): array
    {
        return array_merge($this->trees, $this->objects);
    }

    public function id(): string
    {
        if(!isset($this->id)) $this->generateId();
        return $this->id;
    }

    protected function binded(): XQLObject
    {
        return $this;
    }

    protected function generateId(): void
    {
        $data = get_called_class() . ":" . time() . ":" . microtime();
        $this->id = hash("sha1", $data);
    }

    public function modelKey(bool $plural = false, bool $camelCase = false)
    {
        $cases = $this->cases();
        $arr = ($plural) ? $cases['plural'] : $cases['singular'];
        return ($camelCase) ? $arr['camel'] : $arr['snake'];
    }

}