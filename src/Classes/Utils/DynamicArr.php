<?php

namespace App\XQL\Classes\Utils;

use App\XQL\Classes\Traits\InflectsText;

class DynamicArr
{
    use InflectsText;

    private array $arr;

    public function __construct(array $arr)
    {
        $this->arr = $arr;
    }

    public function exists(string $key): bool
    {
        if(array_key_exists($key, $this->arr)) return true;
        $cases = $this->cases($key);
        $singular = array_values($cases['singular']);
        $plural = array_values($cases['plural']);
        $all = array_merge($singular, $plural);
        $keys = array_keys($this->arr);
        return count(array_intersect($all, $keys)) > 0;
    }

    public function find(string $key): string|bool
    {
        if(array_key_exists($key, $this->arr)) return $key;
        $cases = $this->cases($key);
        $singular = array_values($cases['singular']);
        $plural = array_values($cases['plural']);
        $all = array_merge($singular, $plural);
        $keys = array_keys($this->arr);
        $intersect = array_intersect($all, $keys);
        if(count($intersect) > 0) {
            $unique = array_unique(array_values($intersect));
            if(count($unique) === 1) {
                return $unique[0];
            }
        }
        return false;
    }

}