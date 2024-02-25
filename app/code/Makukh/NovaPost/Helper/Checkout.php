<?php
declare(strict_types=1);

namespace Makukh\NovaPost\Helper;

class Checkout extends Data
{
    /**
     * @var int
     */
    private $base_price = 0;


    /**
     * @return float
     */
    public function getBasePrice()
    {
        return $this->base_price;
    }

    /**
     * @param float $base_price
     */
    public function setBasePrice($base_price)
    {
        $this->base_price = $base_price;
    }

    /**
     * Set shipping base price
     *
     * @param \Magento\Quote\Model\Quote $quoteId
     *
     * @return Checkout
     */
    public function setBasePriceFromQuote($quoteId)
    {
        $price = $this->getParentRatePriceFromQuote($quoteId);
        $this->setBasePrice((double)$price);

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quoteId
     *
     * @return string
     */
    public function getParentRatePriceFromQuote($quoteId)
    {
        $method = $this->getParentRateFromQuote($quoteId);
        if ($method === null) {
            return null;
        }

        return $method->getPriceInclTax();

    }

    /**
     * Get method/option price.
     * Check if total shipping price is not below 0 euro
     *
     * @param  string $carrier
     * @param  string $key
     * @param  bool   $addBasePrice
     *
     * @return float
     */
    public function getMethodPrice(string $carrier, string $key, bool $addBasePrice = true): float
    {
        $value = $this->getCarrierConfig($key, $carrier);

        if ($addBasePrice) {
            // Calculate value
            $value = $this->getBasePrice() + $value;
        }

        return (float)$value;
    }

    /**
     * Get checkout setting
     *
     * @param  string $carrier
     * @param  string $code
     *
     * @return mixed
     */
    public function getCarrierConfig(string $code, string $carrier)
    {
        $value = $this->getConfigValue($carrier . $code);
        if (null === $value) {
            $this->_logger->critical('Can\'t get setting with path:' . $carrier . $code);
            return 0;
        }

        return $value;
    }
}
