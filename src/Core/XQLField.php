<?php

namespace XQL\Core;

use XQL\Core\Types\XQLDataType;

class XQLField extends XQLObject
{

    protected XQLDataType $dataType;
    protected $value;
    protected bool $required = false;

    public function __construct($value = null, $name = null, $dataType = null)
    {
        if(isset($value)) $this->value = $value;
        if(isset($dataType)) $this->dataType = $dataType;
        else $this->dataType = XQLDataType::DYNAMIC;
        parent::__construct($name);
    }

    public function value($value = null) {
        if(isset($value)) $this->value = $value;
        if($this->multiple && isset($this->values) && count($this->values) > 0) return $this->values;
        return $this->value;
    }

    public function type($dataType = null) {
        if(isset($dataType)) $this->dataType = $dataType;
        return $this->dataType;
    }

    public function required($required = null)
    {
        if(isset($required)) $this->required = $required;
        return $this->required;
    }

}