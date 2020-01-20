<?php
/**
 * @category    pimcore5-module-magento2-integration
 * @date        20/01/2020
 * @author      Michał Bolka <mbolka@divante.co>
 * @copyright   Copyright (c) 2020 Divante Ltd. (https://divante.co)
 */
namespace Divante\MagentoIntegrationBundle\Domain\Category;

use Divante\MagentoIntegrationBundle\Domain\Category\Request\GetCategory;
use Divante\MagentoIntegrationBundle\Domain\Common\AbstractMappedObjectService;
use Divante\MagentoIntegrationBundle\Domain\Helper\IntegrationHelper;
use Divante\MagentoIntegrationBundle\Domain\Helper\MapperEventTypes;
use Divante\MagentoIntegrationBundle\Domain\Event\IntegratedObjectEvent;
use Divante\MagentoIntegrationBundle\Domain\Event\PostMappingObjectEvent;
use Divante\MagentoIntegrationBundle\Model\DataObject\IntegrationConfiguration;
use Pimcore\Model\DataObject\Concrete;

/**
 * Class MappedCategoryService
 * @package Divante\MagentoIntegrationBundle\Domain\Category
 */
class MappedCategoryService extends AbstractMappedObjectService
{
    public function getCategories(GetCategory $query)
    {
        $objectsListing = $this->loadObjects($query->id);
        $mappedObjects  = [];
        /** @var array $fetchedIds */
        $missingData = $this->getMissingIds($objectsListing->loadIdList(), $query);
        /** @var Concrete $object */
        foreach ($objectsListing->getObjects() as $object) {
            try {
                $this->permissionChecker->checkElementPermission($object, 'get');
                $configurations = $this->configService->getConfigurations(
                    $object,
                    IntegrationHelper::RELATION_TYPE_CATEGORY,
                    $query->instaceUrl,
                    $query->storeViewId
                );
                if (!$configurations) {
                    $missingData[$object->getId()] = sprintf('Requested object with id %d does not exist.', $object->getId());
                    continue;
                }

                $mappedObjects[$object->getId()] = $this->getMappedObject($object, reset($configurations));
            } catch (\Exception $exception) {
                return ['success' => false, 'missing_objects' => $missingData];
            }
        }

        if (!$mappedObjects) {
            return ['success' => false, 'missing_objects' => $missingData];
        }

        $data = ['data' => $mappedObjects, 'missing_objects' => $missingData, 'success' => true];
        return $data;
    }

    /**
     * @param Concrete                 $object
     * @param IntegrationConfiguration $configuration
     * @return \stdClass
     * @throws \Exception
     */
    protected function getMappedObject(Concrete $object, IntegrationConfiguration $configuration): \stdClass
    {
        $this->eventDispatcher->dispatch(
            new IntegratedObjectEvent($object, $configuration),
            MapperEventTypes::PRE_CATEGORY_MAP
        );

        $out = $this->mapper->getOutObject($object);

        $categoryRoot = $configuration->getCategoryRoot();
        $rootPath     = $categoryRoot->getPath() . $categoryRoot->getKey();
        $out->path    = str_replace($rootPath, '', $object->getPath());

        $this->mapper->loadSelectFieldData($out, $object);
        $mappedObject = $this->mapper->map($out, $configuration, IntegrationHelper::OBJECT_TYPE_CATEGORY);
        $this->configService->setCategoryHierarchyAttribute($mappedObject, $configuration);

        $this->eventDispatcher->dispatch(
            new PostMappingObjectEvent($object, $configuration, $mappedObject),
            MapperEventTypes::POST_CATEGORY_MAP
        );

        return $mappedObject;
    }
}