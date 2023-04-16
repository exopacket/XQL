<?php

namespace App\XQL\Classes;

use App\XQL\Classes\DB\DBX;
use App\XQL\Classes\Types\XQLBindingType;

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

    public function retrieve(array $equals) : void
    {
        $references = (!isset($this->references) || count($this->references) == 0) ? "*" : $this->references;
        $values = DBX::getBindedValues($this->bindFrom, $references, $this->clause, $equals);
        foreach($values as $value) {
            $name = $this->name;
            if($this->multiple) $name = $this->singularName;
            $object = new XQLObject($name);
            $keys = array_keys($value);
            foreach($keys as $key) {
                $field = new XQLField($value[$key], $key);
                $object->appendChild($field);
            }
            $this->objects[] = $object;
        }
    }

    public function groupWhere()
    {
        $this->clause->and();
        $clause = new XQLBindingClause();
        $this->clause->appendGroup($clause);
        return $clause;
    }

    public function orGroupWhere()
    {
        $this->clause->or();
        $clause = new XQLBindingClause();
        $this->clause->appendGroup($clause);
        return $clause;
    }

    public function where(string $column, string $key = null) {
        $this->clause->and();
        $this->clause->append($column, (isset($key)) ? $key : $column);
        return $this;
    }

    public function orWhere(string $column, string $key = null)
    {
        $this->clause->or();
        $this->clause->append($column, $key);
        return $this;
    }

    public function from()
    {
        return $this->bindFrom;
    }

}