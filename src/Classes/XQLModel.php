<?php

namespace App\XQL\Classes;

use App\XQL\Classes\Traits\BuildsModels;
use App\XQL\Classes\Traits\BuildsQueries;
use App\XQL\Classes\Types\XQLBindingType;
use DOMDocument;
use SimpleXMLElement;

abstract class XQLModel extends XQLObject {

    use BuildsQueries, BuildsModels;

    /*
     *
     *   Person
     *      Full Name [nickname]
     *      Gender [preferred pronouns]
     *      Favorite Colors
     *          red [shade]
     *          blue [shade]
     *
     *  =======================================================
     *
     *  <person>
     *      <full_name>Ryan Fitzgerald</full_name>
     *      <gender pronouns="...">male</gender>
     *      <favorite_colors>
     *          <color shade="#f00">red</color>
     *          <color shade="#00f">blue</color>
     *      </favorite_colors>
     *  </person>
     * 
     */

    protected array $trees;
    protected XQLBindingType $outflowType;

    public function __construct() { $this->build(); parent::__construct(); }

    abstract protected function schema(XQLModel $model);

    protected function build()
    {
        $this->schema($this);
    }

    protected function objectify()
    {

    }

    public function export()
    {
        //$this->build();
        $string = $this->xml($this, true);
        echo $string;
    }

    public function children(): array
    {
        return $this->trees ?? [];
    }

    protected function xml(XQLModel $model, bool $formatted = false): string
    {
        $data = $this->binded();
        $xml = new SimpleXMLElement("<{$this->name()}></{$this->name()}>");
        foreach($data->labels() as $label) $xml->addAttribute($label->name(), $label->get());
        foreach($data->children() as $child) {
            $xml = $this->append($child, $xml);
        }
        return ($formatted) ? $this->formatXML($xml->asXML()) : $xml->asXML();
    }

    protected function formatXML(string $input) {
        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;
        $doc->loadXML($input);
        $doc->formatOutput = true;
        return $doc->saveXML();
    }

    protected function append(XQLObject $child, SimpleXMLElement $node): SimpleXMLElement
    {
        foreach($child->children() as $next) {
            echo $next->name . "\n";
            if($next instanceof XQLField) $node->addChild($next->name, $next->value());
            else if($next instanceof XQLObject) $this->append($next, $node->addChild($next->name));
        }
        return $node;
    }

    protected function binded(): XQLObject
    {
        return $this;
    }

}