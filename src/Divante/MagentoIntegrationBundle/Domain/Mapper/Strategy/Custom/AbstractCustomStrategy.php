<?php

namespace Divante\MagentoIntegrationBundle\Domain\Mapper\Strategy\Custom;

use Divante\MagentoIntegrationBundle\Domain\Mapper\Strategy\MapStrategyInterface;
use Pimcore\Model\Webservice\Data\DataObject\Element;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AbstractCustomStrategy
 * @package Divante\MagentoIntegrationBundle\Domain\Mapper\Strategy\Custom
 */
abstract class AbstractCustomStrategy implements CustomStrategyInterface, MapStrategyInterface
{
    /** @var TranslatorInterface  */
    protected $translator;

    /**
     * AbstractMapKeyStrategy constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function canProcess(Element $field, ?array $custom = null): bool
    {
        $canProcess = false;
        if ($custom === null) {
            return $canProcess;
        }
        if (array_key_exists($field->name, $custom)) {
            $custom = $custom[$field->name];
            foreach ($custom as $config) {
                if ($config["strategy"] === $this->getIdentifier()) {
                    $canProcess = true;
                    break;
                }
            }
        }

        return $canProcess;
    }

    /**
     * @param Element     $field
     * @param string|null $language
     * @return string
     */
    protected function getFieldLabel(Element $field, $language): string
    {
        return $this->translator->trans($field->label, [], null, $language);
    }

    /**
     * @param Element $field
     * @param array $custom
     * @return array
     */
    protected function getMagentoFields(Element $field, array $custom): array
    {
        $custom = $custom[$field->name];
        $magentoFields = [];
        foreach ($custom as $config) {
            if ($config["strategy"] === $this->getIdentifier()) {
                $magentoFields[] = $config;
                break;
            }
        }

        return $magentoFields;
    }
}