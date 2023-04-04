<?php

namespace App\XQL\Classes\Traits;

use App\XQL\Classes\XQLField;
use App\XQL\Classes\XQLModel;
use App\XQL\Cloud\Cloud;
use PDO;

trait BuildsQueries
{
    public static function where(): void
    {

    }

    public function create(array $values): XQLModel
    {
        $keys = array_keys($values);
        foreach($keys as $key) {
            $this->{$key} = $values[$key];
            foreach($this->children() as $child) {
                if($child instanceof XQLField && $child->name() === $key) {
                    $child->value($values[$key]);
                }
            }
        }
        Cloud::put("tests/test", $this->export());
        return $this;
    }

    public function update(): void
    {

    }
}