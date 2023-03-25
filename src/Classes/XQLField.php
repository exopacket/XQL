<?php

namespace App\XQL\Classes;

use App\XQL\Classes\Types\XQLDataType;

class XQLField extends XQLObject
{

    protected XQLDataType $dataType;
    protected $value;

    public function __construct($value = null, $name = null, $dataType = null)
    {
        if(isset($value)) $this->value = $value;
        if(isset($dataType)) $this->dataType = $dataType;
        $name = (isset($name)) ? $name : get_class($this);
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

    public function get()
    {
        
    }

}