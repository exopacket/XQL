<?php

namespace XQL\Core;

use SimpleXMLElement;
use XQL\Cloud\Cloud;
use XQL\Core\Traits\BuildsModels;
use XQL\Core\Traits\BuildsQueries;
use XQL\Core\Traits\GeneratesXML;
use XQL\Core\Types\XQLNamingConvention;
use XQL\Core\Utils\DynamicArr;

abstract class XQLModel extends XQLObject {

    use BuildsQueries, BuildsModels, GeneratesXML;

    protected string $id;
    protected bool $static = false;
    protected bool $final = false;
    protected array $primary;

    protected array $hooks = [];
    protected array $attached = [];

    protected array $bindings = [];

    public function __construct(array $data = null) {
        $this->build();
        if(isset($data)) $this->populate($data);
        parent::__construct();
    }

    abstract protected function schema(XQLModel $model);

    protected function build()
    {
        $primary = $this->schema($this);

        if(isset($primary)) {
            if($primary instanceof XQLObject) {
                $this->primary = [
                    "field" => $primary->name(),
                    "object" => $primary
                ];
            } else if(is_array($primary)) {
                $this->primary = $primary;
            }
        }

        $attached = [];

        foreach($this->attached as $attachment) {
            $attached[] = $attachment;
            $model = new $attachment['model_classpath'];
            $children = $model->attached();
            $dups = [];
            foreach($children as $child) {
                $dup = $child;
                $dup['model_tree_path'] = $this->className() . "." . $child['model_tree_path'];
                $dups[] = $dup;
            }
            $attached = array_merge($dups, $attached);
        }

        $this->attached = $attached;
    }

    public function populate(array $data) {
        if(array_key_exists("id", $data) && isset($data['id'])) $id = $data['id'];
        else $id = $this->id ?? $this->generateId();
        $this->id = $id ?? $this->id;
        $path = "results" . "/" . $id . ".xml";
        $this->iterate($this, simplexml_load_string(Cloud::get($path)));
    }

    public function fill($data)
    {
        $this->iterate($this, $data);
    }

    private function iterate(XQLObject $object, SimpleXMLElement $element, $dataObject = null) {

        if(!isset($dataObject)) $dataObject = $this;

        $values = get_object_vars($element->children());
        $dArrSingle = new DynamicArr($values, "singular");
        $dArrMultiple = new DynamicArr($values, "plural");

        $i = 0;
        $children = $object->children();
        foreach($children as $child) {

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
                    throw new Exception($child->name() . " is required and were not found.");
                }
            } else if($child instanceof XQLBinding) {
                if (array_key_exists($child->fieldName(), $values)) {
                    $child->parse($values, $dataObject);
                }

            } else if($child instanceof XQLModel) {


                if($child->isMultiple() && $dArrMultiple->exists($child->name())) {

                    $dKey = $dArrMultiple->find($child->name());

                    $vals = get_object_vars((object) $values[$dKey]);

                    if(is_array(array_values($vals)[0])) {

                        $container = new XQLObject($child->groupName(), true);
                        foreach(array_values($vals)[0] as $value) {
                            $class = get_class($child);
                            $model = new $class();
                            $model->fill($value);
                            $container->appendChild($model);
                        }

                        $object->replace($i, $container);

                    } else {

                        $container = new XQLObject($child->groupName(), true);
                        $class = get_class($child);
                        $model = new $class();
                        $model->fill($values[$dKey]);
                        $container->appendChild($model);

                        $object->replace($i, $container);

                    }

                } else if($dArrSingle->exists($child->name())) {

                    $dKey = $dArrSingle->find($child->name());

                    $class = get_class($child);
                    //TODO get id attribute from xml for model instance
                    $model = new $class();
                    $model->fill($values[$dKey]);

                    $object->replace($i, $model);

                } else if($child->isEnforced()) {

                    throw new \Exception($child->name() . " is required and were not found.");

                }

            } else {
                if($dArrSingle->exists($child->name()) || $dArrMultiple->exists($child->name())) {
                    $dKey = $dArrSingle->find($child->name()) ? $dArrSingle->find($child->name()) : $dArrMultiple->find($child->name()) ;
                    $dataObject->{$child->name()} = (object)[];
                    $this->iterate($child, $values[$dKey], $dataObject->{$child->name()});
                }
            }

            $i++;

        }

    }

    protected function export(): string
    {
        $string = $this->xml(true);
        return $string;
    }

    public function children(): array
    {
        return $this->objects ?? [];
    }

    public function id(): string
    {
//        if(isset($this->primary)) {
//            $object = $this->primary['object'];
//            if($object instanceof XQLBinding) {
//                return $object->{$this->primary['name']};
//            } else {
//                return $object->id();
//            }
//        }
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

    public function attached(): array
    {
        return $this->attached;
    }

    public function isStatic()
    {
        return $this->static;
    }

    public function isFinal()
    {
        return $this->final;
    }

    public function toArray()
    {
        return json_decode(json_encode(simplexml_load_string($this->export())));
    }

    public function get(string $path)
    {
        $path = preg_replace("/[\\\/]/", ".", $path);
        $current = $path;
        if(str_contains($path, ".")) {
            $split = explode(".", $path);
            $next = implode(".", array_splice($split, 1));
            $current = $split[0];
        }

        $value = null;
        if (is_numeric($current)) {
            $current = intval($current);
            $value = $this->values[$current];
        } else {
            foreach($this->children() as $child) {
                if($child->name() == $current) {
                    $value = $child;
                    break;
                }
            }
        }

        if(isset($value)) {
            return (isset($next)) ? $value->get($next) : $value;
        } else {
            return null;
        }
    }

}