<?php
/**
 * @category    pimcore5-module-magento2-integration
 * @date        20/01/2020
 * @author      Michał Bolka <mbolka@divante.co>
 * @copyright   Copyright (c) 2020 DIVANTE (https://divante.co)
 */

namespace Divante\MagentoIntegrationBundle\Domain\Helper;

/**
 * Class ObjectStatusHelper
 * @package Divante\MagentoIntegrationBundle\Domain\Helper
 */
class ObjectStatusHelper
{
    const SYNC_PROPERTY_NAME = 'synchronize-status';

    const SYNC_STATUS_SENT = 'SENT';
    const SYNC_STATUS_OK = 'SUCCESS';
    const SYNC_STATUS_ERROR = 'ERROR';
    const SYNC_STATUS_DELETE = 'DELETED';
}
