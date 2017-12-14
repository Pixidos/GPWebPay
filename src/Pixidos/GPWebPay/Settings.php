<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 11:06
 */
declare(strict_types=1);

namespace Pixidos\GPWebPay;

/**
 * Class Settings
 * @package Pixidos\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */
class Settings
{
    /**
     * @var array
     */
    private $privateKeys;
    /**
     * @var string
     * */
    private $privateKeyPassword;
    
    /**
     * @var array
     */
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
     * @var string defaultGatewayKey
     */
    private $defaultGatewayKey;
    
    /**
     * Settings constructor.
     *
     * @param array  $privateKeys
     * @param array  $privateKeyPassword
     * @param string $publicKey
     * @param string $url
     * @param array  $merchantNumber
     * @param int    $depositFlag
     * @param string $gatewayKey
     */
    public function __construct(
        array $privateKeys,
        array $privateKeyPassword,
        string $publicKey,
        string $url,
        array $merchantNumber,
        int $depositFlag,
        string $gatewayKey
    ) {
        
        $this->privateKeys = $privateKeys;
        $this->privateKeyPassword = $privateKeyPassword;
        
        $this->publicKey = $publicKey;
        $this->url = $url;
        
        $this->merchantNumber = $merchantNumber;
        $this->depositFlag = $depositFlag;
        $this->defaultGatewayKey = $gatewayKey;
    }
    
    
    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
    
    /**
     * @param null|string $gatewayKey
     *
     * @return string
     */
    public function getMerchantNumber(?string $gatewayKey = null): string
    {
        $gatewayKey = $this->getGatewayKey($gatewayKey);
        
        return $this->merchantNumber[$gatewayKey];
    }
    
    /**
     * @param null|string $gatewayKey
     *
     * @return string
     */
    public function getPrivateKey(?string $gatewayKey = null): string
    {
        $gatewayKey = $this->getGatewayKey($gatewayKey);
        
        return $this->privateKeys[$gatewayKey];
    }
    
    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }
    
    /**
     * @param null|string $gatewayKey
     *
     * @return string
     */
    public function getPrivateKeyPassword(?string $gatewayKey = null): string
    {
        $gatewayKey = $this->getGatewayKey($gatewayKey);
        
        return $this->privateKeyPassword[$gatewayKey];
    }
    
    /**
     * @return int
     */
    public function getDepositFlag(): int
    {
        return $this->depositFlag;
    }
    
    /**
     * @return string
     */
    public function getDefaultGatewayKey(): string
    {
        return $this->defaultGatewayKey;
    }
    
    /**
     * @param null|string $gatewayKey
     *
     * @return string
     */
    private function getGatewayKey(?string $gatewayKey = null): string
    {
        if (null === $gatewayKey) {
            $gatewayKey = $this->getDefaultGatewayKey(); //czk config default
        }
        
        return $gatewayKey;
    }
    
}
