<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Concerns;

use DOMDocument;
use DOMException;
use Illuminate\Support\Collection;
use Illuminate\Support\Pluralizer;
use NorseBlue\Xmlify\Contracts\Xmlifiable;
use NorseBlue\Xmlify\DataMapper;
use NorseBlue\Xmlify\DataMappings\AttributeDataMapping;
use NorseBlue\Xmlify\DataMappings\ElementDataMapping;
use NorseBlue\Xmlify\Enums\DataMapperTarget;
use NorseBlue\Xmlify\Enums\ToXmlOption;
use ReflectionException;

trait HandlesXmlify
{
    /**
     * @throws DOMException
     * @throws ReflectionException
     */
    public function ToXml(bool $unformatted = false, ToXmlOption ...$options): string
    {
        $dom = $this->toXmlDom();
        $xml = $dom->saveXML(options: collect($options)->reduce(
            callback: fn (int $chained_options, ToXmlOption $option) => $chained_options | $option->value,
            initial: ToXmlOption::NONE->value),
        );

        if ($unformatted === false) {
            $tidy = tidy_parse_string($xml, [
                'add-xml-space' => false,
                'indent' => true,
                'indent-attributes' => false,
                'indent-cdata' => true,
                'indent-spaces' => 4,
                'input-xml' => true,
                'output-xml' => true,
                'sort-attributes' => true,
                'wrap' => false,
                'wrap-attributes' => false,
                'wrap-sections' => false,
            ]);
            $tidy->cleanRepair();
            $xml = $tidy->root()->value;
        }

        return $xml;
    }

    /**
     * @throws ReflectionException
     * @throws DOMException
     */
    public function ToXmlDom(): DOMDocument
    {
        $mappings = DataMapper::for(static::class)->mappings();

        // TODO: add support for using XML Namespaces?

        // Create DOMDocument with XML Root Node
        $dom = new DOMDocument();
        $dom->encoding = 'UTF-8';
        $dom->appendChild($dom->createElement($mappings[DataMapperTarget::Root->value]->target_name->value()));

        // Add XML Attributes
        $mappings[DataMapperTarget::Attribute->value]->each(function (AttributeDataMapping $mapping) use ($dom) {
            $attribute = $mapping->value->call($this, $mapping->source_name);

            // Attribute is null
            if ($attribute === null) {
                return;
            }

            // Attribute is Stringable
            $attribute_node = $dom->createAttribute($mapping->target_name->value());
            $attribute_node->nodeValue = $attribute->value();
            $dom->documentElement->appendChild($attribute_node);
        });

        // Add XML Elements
        $mappings[DataMapperTarget::Element->value]->each(function (ElementDataMapping $mapping) use ($dom) {
            $element = $mapping->value->call($this, $mapping->source_name);
            $element_node = $dom->createElement($mapping->target_name->value());

            // Element is null
            if ($element === null) {
                $null_attribute = $dom->createAttribute('xsi:nil');
                $null_attribute->nodeValue = 'true';
                $element_node->appendChild($null_attribute);
                $dom->documentElement->appendChild($element_node);

                return;
            }

            // Element is a collection
            if (is_array($element) || $element instanceof Collection) {
                collect($element)->map(function ($item) use ($mapping, $dom, $element_node) {
                    $element_item_name = Pluralizer::singular($mapping->target_name);

                    // Item is a Xmlifiable object
                    if ($item instanceof Xmlifiable) {
                        $element_item = $item->ToXmlDom()->documentElement;
                        $element_item->nodeName = $element_item_name;
                        $element_node->appendChild($dom->importNode($element_item, true));

                        return;
                    }

                    // Item is any other kind of value
                    $element_item = $dom->createElement($element_item_name);
                    $element_item->nodeValue = str($item)->value();
                    $element_node->appendChild($element_item);
                });

                $dom->documentElement->appendChild($element_node);

                return;
            }

            // Element is a Xmlifiable object
            if ($element instanceof Xmlifiable) {
                $element_node = $element->ToXmlDom()->documentElement;
                $dom->documentElement->appendChild($dom->importNode($element_node, true));

                return;
            }

            // Element is Stringable
            if ($mapping->asCData) {
                $element_node->appendChild($dom->createCDATASection($element->value()));
            } else {
                $element_node->nodeValue = $element->value();
            }
            $dom->documentElement->appendChild($element_node);
        });

        return $dom;
    }

    public function __toString(): string
    {
        return $this->ToXml();
    }
}
