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
	 * @param string $privateKey
	 * @param string $privateKeyPassword
	 * @param string $publicKey
	 * @param string $url
	 * @param string $merchantNumber
	 * @param int $depositFlag
	 * @param string $gatewayKey
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
		if (! is_array($privateKey)) {
			$key = $privateKey;
			$privateKey = [];
			$privateKey[$gatewayKey] = $key;
		}

		$this->privateKey = $privateKey;

		if (! is_array($privateKeyPassword)) {
			$pwd = $privateKeyPassword;
			$privateKeyPassword = [];
			$privateKeyPassword[$gatewayKey] = $pwd;
		}

		$this->privateKeyPassword = $privateKeyPassword;

		$this->publicKey = $publicKey;
		$this->url = $url;

		if (! is_array($merchantNumber)) {
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
	 * @return string
	 */
	public function getMerchantNumber($gatewayKey = NULL)
	{
		if (is_null($gatewayKey)) {
			$gatewayKey = $this->getDefaultGatewayKey(); //czk config default
		}
		return $this->merchantNumber[$gatewayKey];
	}

	/**
	 * @param null $gatewayKey
	 * @return string
	 */
	public function getPrivateKey($gatewayKey = NULL)
	{
		if (is_null($gatewayKey)) {
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
	 * @param null $gatewayKey
	 * @return string
	 */
	public function getPrivateKeyPassword($gatewayKey = NULL)
	{
		if (is_null($gatewayKey)) {
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

	public function getDefaultGatewayKey()
	{
		return $this->defaultGatewayKey;
	}

}