<?php
declare(strict_types=1);

namespace Makukh\NovaPost\Model\Checkout;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\FormatInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory;
use Psr\Log\LoggerInterface;

class Carrier extends AbstractCarrier implements CarrierInterface
{
    const CODE = 'novapost';
    protected $_code = self::CODE;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param MethodFactory $rateMethodFactory
     * @param ResultFactory $trackFactory
     * @param ErrorFactory $trackErrorFactory
     * @param StatusFactory $trackStatusFactory
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param CurrencyFactory $currencyFactory
     * @param Data $directoryData
     * @param StockRegistryInterface $stockRegistry
     * @param FormatInterface $localeFormat
     * @param array $data
     */
    public function __construct(
        protected ScopeConfigInterface                                       $scopeConfig,
        protected \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        protected LoggerInterface                                            $logger,
        protected \Magento\Shipping\Model\Rate\ResultFactory                 $rateFactory,
        protected MethodFactory                                              $rateMethodFactory,
        protected ResultFactory                                              $trackFactory,
        protected ErrorFactory                                               $trackErrorFactory,
        protected StatusFactory                                              $trackStatusFactory,
        protected RegionFactory                                              $regionFactory,
        protected CountryFactory                                             $countryFactory,
        protected CurrencyFactory                                            $currencyFactory,
        protected Data                                                       $directoryData,
        protected StockRegistryInterface                                     $stockRegistry,
        protected FormatInterface                                            $localeFormat,
        array                                                                $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function collectRates(RateRequest $request)
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateRequest $result */
        $result = $this->rateFactory->create();

//        $result = $this->addShippingMethods($result);

        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle('Nova Post');
        /* Set method name */
        $method->setMethod($this->_code);
        $method->setMethodTitle('Department');
        $method->setCost(10);
        /* Set shipping charge */
        $method->setPrice(10);
        $result->append($method);
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
            'department'    => 'department/',
            'courier'       => 'courier/',
            'parcel_locker' => 'parcel_locker/',
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

//    /**
//     * @param \Magento\Quote\Model\Quote\Address\RateRequest $result
//     * @return mixed
//     */
//    private function addShippingMethods($result)
//    {
//        $this->package->setDigitalStampSettings();
//        $this->package->setMailboxSettings();
//
//        foreach ($this->getAllowedMethods() as $alias => $settingPath) {
//            $active = $this->myParcelHelper->getConfigValue(Data::XML_PATH_POSTNL_SETTINGS . $settingPath . 'active') === '1';
//            if ($active) {
//                $method = $this->getShippingMethod($alias, $settingPath);
//                $result->append($method);
//            }
//        }
//
//        return $result;
//    }

//    /**
//     * @param $alias
//     * @param  string $settingPath
//     *
//     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
//     */
//    private function getShippingMethod($alias, string $settingPath)
//    {
//        $title = $this->createTitle($settingPath);
//        $price = $this->createPrice($alias, $settingPath);
//
//        $method = $this->_rateMethodFactory->create();
//        $method->setCarrier($this->_code);
//        $method->setCarrierTitle($alias);
//        $method->setMethod($alias);
//        $method->setMethodTitle($title);
//        $method->setPrice($price);
//
//        return $method;
//    }
//
//    /**
//     * Create title for method
//     * If no title isset in config, get title from translation
//     *
//     * @param $settingPath
//     * @return \Magento\Framework\Phrase|mixed
//     */
//    private function createTitle($settingPath)
//    {
//        $title = $this->myParcelHelper->getConfigValue(Data::XML_PATH_POSTNL_SETTINGS . $settingPath . 'title');
//
//        if ($title === null) {
//            $title = __($settingPath . 'title');
//        }
//
//        return $title;
//    }
//
//    /**
//     * Create price
//     * Calculate price if multiple options are chosen
//     *
//     * @param $alias
//     * @param $settingPath
//     * @return float
//     */
//    private function createPrice($alias, $settingPath)
//    {
//        $price = 0;
//
//        $price += $this->myParcelHelper->getMethodPrice($settingPath . 'fee', $alias);
//
//        return $price;
//    }
    /**
     * @inheritDoc
     */
    public function isTrackingAvailable()
    {
        // TODO: Implement isTrackingAvailable() method.
    }
}

