<?php

namespace App\XQL\Core;

use App\XQL\Core\Types\XQLBindingType;
use App\XQL\Core\Types\XQLNamingConvention;
use App\XQL\DB\DBX;

class XQLBinding extends XQLObject
{

    protected string $bindFrom;
    protected array $references;
    protected XQLBindingType $bindType;
    protected XQLBindingClause $clause;

    public function __construct(string $name, string $from, array $references, XQLBindingType $type)
    {
        $this->name = $name;
        $this->bindFrom = $from;
        $this->references = $references;
        $this->bindType = $type;
        $this->clause = new XQLBindingClause();
        parent::__construct($name);
    }

    public static function store(string $name, string|XQLObject $from, array $references): XQLBinding
    {
        if ($from instanceof XQLObject) {
            $type = XQLBindingType::FILE_TO_DB;
            $fromName = $from->name;
        } else {
            $type = XQLBindingType::DB_TO_FILE;
            $fromName = $from;
        }
        //DBX::relationship();
        return new XQLBinding($name, $fromName, $references, $type);
    }

    public function retrieve(XQLModel $model, XQLObject $parent, array $equals) : void
    {
        $references = (!isset($this->references) || count($this->references) == 0) ? "*" : $this->references;
        $values = DBX::getBindedValues($this->bindFrom, $references, $this->clause, $equals);
        foreach($values as $value) {
            $name = $this->singular['snake'];
            $object = new XQLObject($name);
            $object->xpathFromParent($parent->xpath());
            $keys = array_keys($value);
            foreach($keys as $key) {
                $field = new XQLField($value[$key], $key);
                $object->appendChild($field);
                $field->xpathFromParent($object->xpath());
                if($this->isSearchable()) DBX::insertSearchableValue($model, $field);
            }
            $this->objects[] = $object;
        }
    }

    public function parse(array $values, &$dataObject)
    {
        foreach($values as $element) {
            $items = array_values(get_object_vars($element))[0];
            foreach($items as $item) {
                $value = get_object_vars($item);
                $name = $this->singular['snake'];
                $object = new XQLObject($name);
                $keys = array_keys($value);
                $dataObject->{$name} = (object)[];
                foreach ($keys as $key) {
                    $field = new XQLField($value[$key], $key);
                    $object->appendChild($field);
                    $dataObject->{$name}->{$field->name()} = $field->value();
                }
                $this->objects[] = $object;
            }
        }
    }

    public function where(string|array|callable $column, string $key = null) {
        if(is_array($column)) {
            $hasKeys = array_keys($column) !== range(0, count($column) - 1);
            $keys = ($hasKeys) ? array_keys($column) : $column;
            $values = ($hasKeys) ? array_values($column) : $column;
            for ($i = 0; $i < count($column); $i++) {
                $this->clause->and();
                $this->clause->append($keys[$i], $values[$i]);
            }
        } else if(is_callable($column)) {
            $this->clause->and();
            $clause = new XQLBindingClause();
            $this->clause->appendGroup($clause);
            $column($clause);
        } else {
            $this->clause->and();
            $this->clause->append($column, (isset($key)) ? $key : $column);
        }
        return $this;
    }

    public function orWhere(string|array|callable $column, string $key = null)
    {
        if(is_array($column)) {
            $hasKeys = array_keys($column) !== range(0, count($column) - 1);
            $keys = ($hasKeys) ? array_keys($column) : $column;
            $values = ($hasKeys) ? array_values($column) : $column;
            for($i=0; $i<count($column); $i++) {
                $this->clause->or();
                $this->clause->append($keys[$i], $values[$i]);
            }
        } else if(is_callable($column)) {
            $this->clause->or();
            $clause = new XQLBindingClause();
            $this->clause->appendGroup($clause);
            $column($clause);
        } else {
            $this->clause->or();
            $this->clause->append($column, (isset($key)) ? $key : $column);
        }
        return $this;
    }

    public function fieldName()
    {
        return $this->snake($this->name);
    }

}