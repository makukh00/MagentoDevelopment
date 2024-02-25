<?php
declare(strict_types=1);

namespace Makukh\NovaPost\Model\Checkout;

use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Makukh\NovaPost\Helper\Checkout;
use Makukh\NovaPost\Helper\Data;

class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    const CODE = 'novapost';
    protected $_code = self::CODE;

    /**
     * @var Checkout
     */
    private $myHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Xml\Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param Checkout $myHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory,
        \Psr\Log\LoggerInterface                                    $logger,
        \Magento\Framework\Xml\Security                             $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory            $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory                  $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory              $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory        $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory       $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory                      $regionFactory,
        \Magento\Directory\Model\CountryFactory                     $countryFactory,
        \Magento\Directory\Model\CurrencyFactory                    $currencyFactory,
        \Magento\Directory\Helper\Data                              $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface        $stockRegistry,
        Checkout                                                    $myHelper,
        array                                                       $data = [],
    ) {
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );

        $this->myHelper = $myHelper;
    }

    public function collectRates(RateRequest $request)
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateRequest $result */
        $result = $this->rateFactory->create();
        $result = $this->addShippingMethods($result);

        return $result;
    }

    public function proccessAdditionalValidation(DataObject $request)
    {
        return true;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public static function getMethods()
    {
        return [
            'department'    => 'department__',
            'courier'       => 'courier__',
            'parcel_locker' => 'parcel_locker__',
        ];
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return self::getMethods();
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $result
     * @return mixed
     */
    private function addShippingMethods($result)
    {
        foreach ($this->getAllowedMethods() as $alias => $settingPath) {
            $method = $this->getShippingMethod($alias, $settingPath);
            $result->append($method);
        }
        return $result;
    }

    /**
     * @param $alias
     * @param  string $settingPath
     *
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    private function getShippingMethod($alias, string $settingPath)
    {
        $title = $this->createTitle($settingPath);
        $price = $this->createPrice($alias, $settingPath);

        $method = $this->_rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($alias);
        $method->setMethod($alias);
        $method->setMethodTitle($title);
        $method->setPrice($price);

        return $method;
    }

    /**
     * Create title for method
     * If no title isset in config, get title from translation
     *
     * @param $settingPath
     * @return \Magento\Framework\Phrase|mixed
     */
    private function createTitle($settingPath)
    {
        $title = $this->myHelper->getConfigValue(Data::XML_PATH_NOVAPOST_SETTINGS . $settingPath . 'title');

        if ($title === null) {
            $title = __($settingPath . 'title');
        }

        return $title;
    }

    /**
     * Create price
     * Calculate price if multiple options are chosen
     *
     * @param $alias
     * @param $settingPath
     * @return float
     */
    private function createPrice($alias, $settingPath)
    {
        $price = 0;

        $price += $this->myHelper->getMethodPrice($settingPath . 'fee', $alias);

        return $price;
    }

    /**
     * @inheritDoc
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
    }
}

