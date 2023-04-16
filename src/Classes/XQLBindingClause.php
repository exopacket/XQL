<?php

namespace App\XQL\Classes;

class XQLBindingClause
{

    protected array $conditions = [];

    public function get(): array
    {
        $conditions = [];
        foreach($this->conditions as $condition) {
            if($condition instanceof XQLBindingClause) $conditions[] = $condition->get();
            else $conditions[] = $condition;
        }
        return $conditions;
    }

    public function where(string $column, string $key = null) {
        $this->and();
        $this->append($column, $key);
    }

    public function orWhere(string $column, string $key = null)
    {
        $this->or();
        $this->append($column, $key);
    }

    public function append(string $column, string $key) {
        $this->conditions[] = [
            "column" => $column,
            "key" => $key,
            "value" => null
        ];
        return $this;
    }

    public function appendGroup(XQLBindingClause $clause)
    {
        $this->conditions[] = $clause;
    }

    public function or()
    {
        if(count($this->conditions) === 0) return $this;
        $this->conditions[] = [
            "condition" => "or"
        ];
        return $this;
    }

    public function and()
    {
        if(count($this->conditions) === 0) return $this;
        $this->conditions[] = [
            "condition" => "or"
        ];
        return $this;
    }

}