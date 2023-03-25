<?php

namespace App\XQL\Classes\Traits;

use App\XQL\Classes\XQLField;
use App\XQL\Classes\XQLObject;

trait GeneratesXML
{

    protected function xml(XQLModel $model): string
    {
        $data = $this->binded($model);
        $xml = new SimpleXMLElement("<{$data->name()}></{$data->name()}>");
        foreach($data->labels() as $label) $xml->addAttribute($label->name(), $label->get());
        foreach($data->children() as $child) {
            $xml = $this->append($child, $xml);
        }
        return $xml->asXML();
    }

    protected function append(XQLObject $child, SimpleXMLElement $xml): SimpleXMLElement
    {
        foreach($child->children() as $next) {
            if($next instanceof XQLField) $xml->addChild($next->name(), $next->get());
            else $node = $xml->addChild($next->name());
            if(isset($node)) $xml = $this->append($next, $node);
       }
        return $xml;
    }

    protected function binded(): XQLObject
    {
        return $this;
    }

}