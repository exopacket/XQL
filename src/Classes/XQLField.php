<?php

namespace App\XQL\Classes;

use App\XQL\Classes\Types\XQLDataType;

class XQLField extends XQLObject
{

    protected XQLDataType $dataType;
    protected $value;
    protected bool $required = false;

    public function __construct($value = null, $name = null, $dataType = null)
    {
        if(isset($value)) $this->value = $value;
        if(isset($dataType)) $this->dataType = $dataType;
        parent::__construct($name);
    }

    public function value($value = null) {
        if(isset($value)) $this->value = $value;
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