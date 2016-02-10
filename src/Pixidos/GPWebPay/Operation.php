<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 11:18
 */

namespace Pixidos\GPWebPay;

use Pixidos\GPWebPay\Exceptions\GPWebPayException;
use Pixidos\GPWebPay\Exceptions\InvalidArgumentException;

/**
 * Class Operation
 * @package Pixidos\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */
class Operation
{

    const EUR = 978;
    const CZK = 203;

    /**
     * @var int $orderNumber
     */
    private $orderNumber;

    /**
     * @var int $amount
     */
    private $amount;

    /**
     * @var int $currency
     */
    private $currency;

    /**
     * @var  string $description
     */
    private $description;
    /**
     * @var  string $md
     */
    private $md;
    /**
     * @var  int $merordernum
     */
    private $merordernum;
    /**
     * @var null $responseUrl
     */
    private $responseUrl;

    /**
     * @var string $gatewayKey
     */
    private $gatewayKey = NULL;


    /**
     * Operation constructor.
     * @param string $orderNumber max. length is 15
     * @param int $amount
     * @param int $currency max. length is 3
     * @param null $gatewayKey
     * @param null $responseUrl
     * @throws InvalidArgumentException
     */
    public function __construct($orderNumber, $amount, $currency, $gatewayKey = NULL, $responseUrl = NULL)
    {

        $this->setOrderNumber($orderNumber);
        $this->setAmount($amount);
        $this->setCurrency($currency);

        $this->gatewayKey = $gatewayKey;

        $this->md = $gatewayKey;

        if ($responseUrl)
            $this->responseUrl = $responseUrl;


    }

    /**
     * @param int $orderNumber
     * @throws InvalidArgumentException
     */
    private function setOrderNumber($orderNumber)
    {
        if (strlen($orderNumber) > 15)
            throw new InvalidArgumentException('ORDERNUMBER max. length is 15! ' . strlen($orderNumber) . ' given');
        if (!is_int($orderNumber))
            throw new InvalidArgumentException('ORDERNUMBER must by type of int ' . gettype($orderNumber) . ' given');
        $this->orderNumber = $orderNumber;
    }

    /**
     * @param int | float $amount
     * @return $this
     * @throws InvalidArgumentException
     */
    private function setAmount($amount)
    {
        if (!is_int($amount))
            if (!is_float($amount))
                throw new InvalidArgumentException('AMOUNT must by type of INT or FLOAT !' . gettype($amount) . ' given');
        $this->amount = $amount * 100;

        return $this;
    }

    /**
     * @param $currency
     * @throws GPWebPayException
     */
    private function setCurrency($currency)
    {
        if (!is_int($currency))
            throw new InvalidArgumentException('CURRENCY must by INT ! ' . gettype($currency) . ' given');
        if (strlen($currency) > 15)
            throw new InvalidArgumentException('CURRENCY code max. length is 3! ' . strlen($currency) . ' given');

        $this->currency = $currency;
    }

    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return int
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return null | string
     */
    public function getResponseUrl()
    {
        return ($this->responseUrl)
            ? $this->responseUrl
            : NULL;
    }

    /**
     * @param $url
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setResponseUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL))
            throw new InvalidArgumentException('URL is Invalid');

        $this->responseUrl = $url;

        return $this;
    }

    /**
     * @return null | string
     */
    public function getMd()
    {
        return ($this->md)
            ? $this->md
            : NULL;
    }

    /**
     * @param string $md max. length is 255!
     * @return $this
     * @throws GPWebPayException
     */
    public function setMd($md)
    {
        if (strlen($md) > 250)
            throw new GPWebPayException('MD max. length is 250! ' . strlen($md) . ' given');

        $this->md .= '|'.$md;

        return $this;
    }

    /**
     * @return null | string
     */
    public function getDescription()
    {
        return ($this->description)
            ? $this->description
            : NULL;
    }

    /**
     * @param string $description max. length is 255
     * @return $this
     * @throws GPWebPayException
     */
    public function setDescription($description)
    {
        if (strlen($description) > 255)
            throw new GPWebPayException('DESCRIPTION max. length is 255! ' . strlen($description) . ' given');

        $this->description = $description;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMerOrderNum()
    {
        return ($this->merordernum)
            ? $this->merordernum
            : NULL;
    }

    /**
     * @param string $merordernum max. length is 30
     * @return $this
     * @throws GPWebPayException
     */
    public function setMerOrderNum($merordernum)
    {
        if (strlen($merordernum) > 30)
            throw new GPWebPayException('MERORDERNUM max. length is 30! ' . strlen($merordernum) . ' given');
        $this->merordernum = $merordernum;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getGatewayKey()
    {
        return $this->gatewayKey;
    }
}
