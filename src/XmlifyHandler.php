<?php

namespace NorseBlue\Xmlify;

use DOMDocument;
use DOMElement;
use DOMException;
use Illuminate\Support\Collection;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Stringable;
use NorseBlue\Xmlify\Contracts\Xmlifiable;
use NorseBlue\Xmlify\DataMappings\AttributeDataMapping;
use NorseBlue\Xmlify\DataMappings\ElementDataMapping;
use NorseBlue\Xmlify\DataMappings\RootDataMapping;
use NorseBlue\Xmlify\Enums\DataMapperTarget;
use ReflectionException;
use RuntimeException;

/**
 * @template T of object
 */
readonly class XmlifyHandler
{
    /**
     * @var DataMapper<object>
     */
    private DataMapper $mapper;

    public function __construct(public object $xmlifiable)
    {
        $this->mapper = DataMapper::for($xmlifiable::class);
    }

    /**
     * @throws ReflectionException
     * @throws DOMException
     */
    public function run(): DOMDocument
    {
        $dom = new DOMDocument();
        $dom->encoding = 'utf-8';

        $mappings = $this->mapper->mappings();

        $this->processRootMapping($dom, $mappings[DataMapperTarget::Root->value]);

        $this->processAttributeMappings($dom, $mappings[DataMapperTarget::Attribute->value]);

        $this->processElementMappings($dom, $mappings[DataMapperTarget::Element->value]);

        return $dom;
    }

    private function buildQualifiedName(RootDataMapping|AttributeDataMapping|ElementDataMapping $mapping): Stringable
    {
        if ($mapping->namespace->isEmpty()) {
            return $mapping->target_name;
        }

        return $mapping->namespace->append(':', $mapping->target_name);
    }

    /**
     * @throws RuntimeException
     */
    private function ensureRootElement(DOMDocument $dom): DOMElement
    {
        if ($dom->documentElement === null) {
            throw new RuntimeException('DOMDocument has no root element.');
        }

        return $dom->documentElement;
    }

    /**
     * @throws DOMException
     */
    private function processRootMapping(DOMDocument $dom, RootDataMapping $root_mapping): void
    {
        $root_element = ($root_mapping->namespace->isEmpty())
            ? $dom->createElement($this->buildQualifiedName($root_mapping)->value())
            : $dom->createElementNS($root_mapping->namespaces[$root_mapping->namespace->value()], $this->buildQualifiedName($root_mapping)->value());

        foreach ($root_mapping->namespaces as $ns => $uri) {
            $root_element->setAttributeNS(
                namespace: 'http://www.w3.org/2000/xmlns/',
                qualifiedName: "xmlns:$ns",
                value: $uri,
            );
        }

        $dom->appendChild($root_element);
    }

    /**
     * @param  Collection<int, AttributeDataMapping>  $attribute_mappings
     *
     * @throws DOMException
     */
    private function processAttributeMappings(DOMDocument $dom, Collection $attribute_mappings): void
    {
        $attribute_mappings->each(function (AttributeDataMapping $mapping) use ($dom): void {
            $attribute_value = $mapping->value->call($this->xmlifiable, $mapping->source_name);

            match (true) {
                $attribute_value === null => false,
                default => $this->appendAttributeIsStringable($dom, $mapping, $attribute_value),
            };
        });
    }

    /**
     * @throws DOMException
     */
    private function appendAttributeIsStringable(DOMDocument $dom, AttributeDataMapping $mapping, Stringable $attribute_value): void
    {
        $rootElement = $this->ensureRootElement($dom);

        $attribute_node = ($mapping->namespace->isEmpty())
            ? $dom->createAttribute($this->buildQualifiedName($mapping)->value())
            : $dom->createAttributeNS($dom->lookupNamespaceURI($mapping->namespace->value()), $this->buildQualifiedName($mapping)->value());

        $attribute_node->nodeValue = $attribute_value->value();

        $rootElement->appendChild($attribute_node);
    }

    /**
     * @param  Collection<int, ElementDataMapping>  $element_mappings
     *
     * @throws DOMException
     */
    private function processElementMappings(DOMDocument $dom, Collection $element_mappings): void
    {
        $element_mappings->each(function (ElementDataMapping $mapping) use ($dom): void {
            $element_value = $mapping->value->call($this->xmlifiable, $mapping->source_name);
            $element_node = ($mapping->namespace->isEmpty())
                ? $dom->createElement($this->buildQualifiedName($mapping)->value())
                : $dom->createElementNS($dom->lookupNamespaceURI($mapping->namespace->value()), $this->buildQualifiedName($mapping)->value());

            foreach ($mapping->namespaces as $ns => $uri) {
                $element_node->setAttributeNS(
                    namespace: 'http://www.w3.org/2000/xmlns/',
                    qualifiedName: "xmlns:$ns",
                    value: $uri,
                );
            }

            match (true) {
                $element_value === null => $this->appendNodeElementIsNull($dom, $element_node),
                $element_value instanceof Xmlifiable => $this->appendNodeElementIsXmlifiable($dom, $element_value, $mapping),
                is_array($element_value) || $element_value instanceof Collection => $this->appendNodeElementIsCollection($dom, $element_node, $mapping, $element_value),
                default => $this->appendNodeElementIsStringable($dom, $element_node, $mapping, $element_value),
            };
        });
    }

    /**
     * @throws DOMException
     */
    private function appendNodeElementIsNull(DOMDocument $dom, DOMElement $element_node): void
    {
        $rootElement = $this->ensureRootElement($dom);

        $null_attribute = $dom->createAttribute('xsi:nil');
        $null_attribute->nodeValue = 'true';
        $element_node->appendChild($null_attribute);

        $rootElement->appendChild($element_node);
    }

    private function appendNodeElementIsXmlifiable(DOMDocument $dom, Xmlifiable $element_value, ElementDataMapping $mapping): void
    {
        $root_element = $this->ensureRootElement($dom);

        $element_node = $element_value->ToXmlDom()->documentElement;
        if ($element_node !== null) {
            $root_element->appendChild($dom->importNode($element_node, true));
        }
    }

    /**
     * @param  array<mixed>|Collection<int, mixed>  $element_value
     *
     * @throws RuntimeException
     */
    private function appendNodeElementIsCollection(DOMDocument $dom, DOMElement $element_node, ElementDataMapping $mapping, array|Collection $element_value): void
    {
        $rootElement = $this->ensureRootElement($dom);

        collect($element_value)->map(function ($item) use ($dom, $element_node, $mapping): void {
            // TODO: if the parent has a namespace, add that namespace to childs
            $element_item_name = Pluralizer::singular($mapping->target_name);

            if ($item instanceof Xmlifiable) {
                // Item is a Xmlifiable object
                $element_item = $item->ToXmlDom()->documentElement;
                if ($element_item !== null) {
                    $element_item->nodeName = $element_item_name;
                    $element_node->appendChild($dom->importNode($element_item, true));
                }
            } else {
                // Item is any other kind of value
                $element_item = $dom->createElement($element_item_name);
                $element_item->nodeValue = str($item)->value();
                $element_node->appendChild($element_item);
            }
        });

        $rootElement->appendChild($element_node);
    }

    private function appendNodeElementIsStringable(DOMDocument $dom, DOMElement $element_node, ElementDataMapping $mapping, Stringable $element_value): void
    {
        $rootElement = $this->ensureRootElement($dom);

        if ($mapping->asCData) {
            $element_node->appendChild($dom->createCDATASection($element_value->value()));
        } else {
            $element_node->nodeValue = $element_value->value();
        }

        $rootElement->appendChild($element_node);
    }
}
