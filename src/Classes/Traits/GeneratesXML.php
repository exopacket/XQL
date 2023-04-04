<?php

namespace App\XQL\Classes\Traits;

use App\XQL\Classes\XQLField;
use App\XQL\Classes\XQLModel;
use App\XQL\Classes\XQLObject;
use DOMDocument;
use SimpleXMLElement;

trait GeneratesXML
{
    protected function xml(XQLModel $model, bool $formatted = false): string
    {
        $data = $this->binded();
        $xml = new SimpleXMLElement("<{$this->name()}></{$this->name()}>");
        foreach($data->labels() as $label) $xml->addAttribute($label->name(), $label->get());
        foreach($data->attributes() as $attr) $xml->addAttribute($attr->name(), $attr->get());
        foreach($data->children() as $child) {
            $xml = $this->append($child, $xml);
        }
        return ($formatted) ? $this->formatXml($xml->asXML()) : $xml->asXML();
    }

    protected function formatXml(string $input) {
        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;
        $doc->loadXML($input);
        $doc->formatOutput = true;
        return $doc->saveXML();
    }

    protected function append(XQLObject $child, SimpleXMLElement $node): SimpleXMLElement
    {
        if(count($child->children()) > 0) {
            foreach ($child->children() as $next) {
                if ($next instanceof XQLField) $node->addChild($next->name(), $next->value());
                else if ($next instanceof XQLObject) $this->append($next, $node->addChild($next->name()));
            }
        } else {
            if ($child instanceof XQLField) $node->addChild($child->name(), $child->value());
        }
        return $node;
    }
}