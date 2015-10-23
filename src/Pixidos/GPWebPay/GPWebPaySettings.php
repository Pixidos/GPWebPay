<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 11:06
 */

namespace Pixidos\GPWebPay;

/**
 * Class GPWebPaySettings
 * @package Pixidos\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */

class GPWebPaySettings
{
    /** @var string */
    private $privateKey;

    /** @var string */
    private $privateKeyPassword;

    /** @var string */
    private $publicKey;

    /**
     * @var  string $url
     */
    private $url;
    /**
     * @var  string $merchantNumber
     */
    private $merchantNumber;

    /**
     * @var int $depositFlag
     */
    private $depositFlag;

    /**
     * GPWebPaySettings constructor.
     * @param string $privateKey
     * @param string $privateKeyPassword
     * @param string $publicKey
     * @param string $url
     * @param string $merchantNumber
     * @param int $depositFlag
     */
    public function __construct($privateKey, $privateKeyPassword, $publicKey, $url, $merchantNumber, $depositFlag)
    {
        $this->privateKey = $privateKey;
        $this->privateKeyPassword = $privateKeyPassword;
        $this->publicKey = $publicKey;
        $this->url = $url;
        $this->merchantNumber = $merchantNumber;
        $this->depositFlag = $depositFlag;
    }


    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getMerchantNumber()
    {
        return $this->merchantNumber;
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getPrivateKeyPassword()
    {
        return $this->privateKeyPassword;
    }

    /**
     * @return int
     */
    public function getDepositFlag()
    {
        return $this->depositFlag;
    }

}