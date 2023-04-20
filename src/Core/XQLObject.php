<?php

namespace XQL\Core;

use XQL\Core\Traits\BuildsSchemas;
use XQL\Core\Traits\InflectsText;
use XQL\Core\Types\XQLNamingConvention;
use SimpleXMLElement;

class XQLObject
{
    use BuildsSchemas, InflectsText;

    protected array $objects;
    protected array $labels;
    protected array $checksums;
    protected array $values;
    protected string $name;
    protected array $singular;
    protected array $plural;
    protected string $xpath;
    protected bool $searchable = false;
    protected bool $multiple = false;
    protected bool $enforced = false;

    public function __construct($name = null)
    {
        $className = $this->className();
        if($className == "XQLObject" && !isset($name)) throw Exception("Basic XQLObject must be constructed with a name.");
        $this->name = $name ?? $className;
        $cases = $this->cases();
        $this->plural = $cases['plural'];
        $this->singular = $cases['singular'];
    }

    public function name(): string {
        return $this->name ?? $this->className();
    }

    public function groupName()
    {
        return $this->plural['snake'];
    }

    public function fieldName()
    {
        return $this->singular['snake'];
    }

    public function labels(): array {
        return $this->labels ?? [];
    }

    public function children(): array {
        return $this->objects ?? [];
    }

    public function checksums(): array
    {
        $this->checksums ?? $this->checksums = [];
        $this->hmac();
        return $this->checksums;
    }

    public function xpath(string $path = null)
    {
        if(isset($path)) $this->xpath = $path;
        return $this->xpath;
    }

    public function xpathFromParent(string $parentPath)
    {
        if($parentPath === "") $this->xpath = $this->fieldName();
        else $this->xpath = $parentPath . "/" . $this->fieldName();
        return $this->xpath;
    }

    private function hmac(): void
    {
        return;
        $xml = new SimpleXMLElement("<{$this->name()}></{$this->name()}>");
        foreach($this->children() as $child) {
            if(is_array($child) && count($child) === 1) $child = array_values($child)[0];
            $this->traverse($child, ($child instanceof XQLField) ? $xml : $xml->addChild($child->name()));
        }
        $key = "abc";// config("XQL_HMAC_KEY");
        $content = $xml->asXML();
        $this->checksums[] = new XQLAttribute("md", substr(hash_hmac("sha256", $content, $key), 0, 40));
    }

    protected function traverse(XQLObject $child, SimpleXMLElement $node): SimpleXMLElement
    {
        if(count($child->children()) > 0) {
            foreach ($child->children() as $next) {
                if(is_array($next) && count($next) === 1) $next = array_values($next)[0];
                if ($next instanceof XQLField) $node->addChild($next->name(), $next->value());
                else if ($next instanceof XQLObject) $this->traverse($next, $node->addChild($next->name()));
            }
        } else {
            if ($child instanceof XQLField) $node->addChild($child->name(), $child->value());
        }
        return $node;
    }

    public function appendMultiple($value)
    {
        $this->values[] = $value;
    }

    public function appendChild(XQLObject $child)
    {
        if(!isset($this->objects)) $this->objects = [$child];
        else $this->objects[] = $child;
    }

    /**
     * @return bool
     */
    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    /**
     * @return bool
     */
    public function isEnforced(): bool
    {
        return $this->enforced;
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

}