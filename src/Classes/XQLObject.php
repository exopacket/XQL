<?php

namespace App\XQL\Classes;

use App\XQL\Classes\Traits\BuildsSchemas;
use SimpleXMLElement;

class XQLObject
{
    use BuildsSchemas;

    protected array $objects;
    protected array $labels;
    protected array $checksums;
    protected array $values;
    protected string $name;
    protected bool $cached = false;
    protected bool $multiple = false;
    protected bool $enforced = false;

    public function __construct($name = null)
    {
        $basename = $name ?? (new \ReflectionClass($this))->getShortName();
        if($basename != "XQLObject") $name = $basename;
        $this->name = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $name)), '_');
    }

    public function name(): string {
        return $this->name ?? [];
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

    public function appendValue($value)
    {
        $this->values[] = $value;
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