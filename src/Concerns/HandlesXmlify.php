<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Concerns;

use DOMDocument;
use DOMException;
use NorseBlue\Xmlify\Enums\ToXmlOption;
use NorseBlue\Xmlify\XmlifyHandler;
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
        $handler = new XmlifyHandler($this);

        return $handler->run();
    }

    /**
     * @throws DOMException
     * @throws ReflectionException
     */
    public function __toString(): string
    {
        return $this->ToXml();
    }
}
