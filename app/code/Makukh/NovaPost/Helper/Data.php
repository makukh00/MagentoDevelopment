<?php
declare(strict_types=1);

namespace Makukh\NovaPost\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Makukh\NovaPost\Model\Carrier\CarrierNovaPost;

class Data extends AbstractHelper
{
    public const MODULE_NAME = 'MyParcelNL_Magento';
    public const XML_PATH_GENERAL = 'makukh_nova_post_general/';
    public const XML_PATH_NOVAPOST_SETTINGS = 'makukh_nova_post_settings/';
    public const CARRIERS_XML_PATH_MAP = [
        CarrierNovaPost::NAME => self::XML_PATH_NOVAPOST_SETTINGS
    ];

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Get settings by field
     *
     * @param       $field
     * @param  null $storeId
     *
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Get general settings
     *
     * @param  string   $code
     * @param  null|int $storeId
     *
     * @return mixed
     */
    public function getGeneralConfig(string $code = '', int $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_GENERAL . $code, $storeId);
    }

    /**
     * Get default settings
     *
     * @param  string $carrier
     * @param  string $code
     * @param  null   $storeId
     *
     * @return mixed
     */
    public function getStandardConfig(string $carrier, string $code = '', $storeId = null)
    {
        return $this->getConfigValue(self::CARRIERS_XML_PATH_MAP[$carrier] . $code, $storeId);
    }

    /**
     * Get carrier setting
     *
     * @param  string $code
     * @param  string $carrier
     *
     * @return mixed
     */
    public function getCarrierConfig(string $code, string $carrier)
    {
        $settings = $this->getConfigValue($carrier . $code);

        if (null === $settings) {
            $value = $this->getConfigValue($carrier . $code);

            if (null === $value) {
                $this->_logger->critical('Can\'t get setting with path:' . $carrier . $code);
            }

            return $value;
        }

        return $settings;
    }
}
