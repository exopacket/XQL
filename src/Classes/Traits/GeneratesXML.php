<?php

namespace App\XQL\Classes\Traits;

use App\XQL\Classes\XQLField;
use App\XQL\Classes\XQLObject;
use DOMDocument;
use SimpleXMLElement;

trait GeneratesXML
{
    protected function xml(bool $formatted = false): string
    {
        $data = $this->binded();
        $xml = new SimpleXMLElement("<{$this->name()}></{$this->name()}>");
        foreach($data->labels() as $label) $xml->addAttribute($label->name(), $label->get());
        foreach($data->checksums() as $attr) if(!isset($xml[$attr->name()])) $xml->addAttribute($attr->name(), $attr->get());
        foreach($data->children() as $child) {
            if(is_array($child) && count($child) === 1) $child = array_values($child)[0];
            $this->append($child, ($child instanceof XQLField) ? $xml : $xml->addChild($child->name()));
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
                if(is_array($next) && count($next) === 1) $next = array_values($next)[0];
                if ($next instanceof XQLField) $node->addChild($next->name(), $next->value());
                else if ($next instanceof XQLObject) $this->append($next, $node->addChild($next->name()));
            }
        } else {
            if ($child instanceof XQLField) $node->addChild($child->name(), $child->value());
        }
        foreach($child->checksums() as $attr) if(!isset($node[$attr->name()])) $node->addAttribute($attr->name(), $attr->get());
        return $node;
    }
}