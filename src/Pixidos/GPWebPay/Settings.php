<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 11:06
 */

namespace Pixidos\GPWebPay;

/**
 * Class Settings
 * @package Pixidos\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */
class Settings
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
    
    private $defaultGatewayKey;
    
    /**
     * Settings constructor.
     *
     * @param string|array $privateKey
     * @param string|array $privateKeyPassword
     * @param string|array $publicKey
     * @param string       $url
     * @param string|array $merchantNumber
     * @param int          $depositFlag
     * @param string       $gatewayKey
     */
    public function __construct(
        $privateKey,
        $privateKeyPassword,
        $publicKey,
        $url,
        $merchantNumber,
        $depositFlag,
        $gatewayKey
    ) {
        $gatewayKey = (string)$gatewayKey;
        if (!is_array($privateKey)) {
            $key = $privateKey;
            $privateKey = [];
            $privateKey[$gatewayKey] = $key;
        }
        
        $this->privateKey = $privateKey;
        
        if (!is_array($privateKeyPassword)) {
            $pwd = $privateKeyPassword;
            $privateKeyPassword = [];
            $privateKeyPassword[$gatewayKey] = $pwd;
        }
        
        $this->privateKeyPassword = $privateKeyPassword;
        
        $this->publicKey = $publicKey;
        $this->url = $url;
        
        if (!is_array($merchantNumber)) {
            $merchant = $merchantNumber;
            $merchantNumber = [];
            $merchantNumber[$gatewayKey] = $merchant;
        }
        
        $this->merchantNumber = $merchantNumber;
        $this->depositFlag = $depositFlag;
        $this->defaultGatewayKey = $gatewayKey;
    }
    
    
    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * @param null $gatewayKey
     *
     * @return string
     */
    public function getMerchantNumber($gatewayKey = null)
    {
        if (null === $gatewayKey) {
            $gatewayKey = $this->getDefaultGatewayKey(); //czk config default
        }
        
        return $this->merchantNumber[$gatewayKey];
    }
    
    /**
     * @param null $gatewayKey
     *
     * @return string
     */
    public function getPrivateKey($gatewayKey = null)
    {
        if (null === $gatewayKey) {
            $gatewayKey = $this->getDefaultGatewayKey(); //czk config default
        }
        
        return $this->privateKey[$gatewayKey];
    }
    
    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }
    
    /**
     * @param null|string $gatewayKey
     *
     * @return string
     */
    public function getPrivateKeyPassword($gatewayKey = null)
    {
        if (null === $gatewayKey) {
            $gatewayKey = $this->getDefaultGatewayKey(); //czk config default
        }
        
        return $this->privateKeyPassword[$gatewayKey];
    }
    
    /**
     * @return int
     */
    public function getDepositFlag()
    {
        return $this->depositFlag;
    }
    
    /**
     * @return string
     */
    public function getDefaultGatewayKey()
    {
        return $this->defaultGatewayKey;
    }
    
}
