<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 10:38
 */

namespace Pixidos\GPWebPay;

use Pixidos\GPWebPay\Exceptions\SignerException;
use Pixidos\GPWebPay\Intefaces\ISigner;

/**
 * Class Signer
 * @package Pixidos\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */
class Signer implements ISigner
{

	/** @var string */
	private $privateKey;

	/** @var resource */
	private $privateKeyResource;

	/** @var string */
	private $privateKeyPassword;

	/** @var string */
	private $publicKey;

	/** @var resource */
	private $publicKeyResource;

	/**
	 * @param $privateKey
	 * @param $privateKeyPassword
	 * @param $publicKey
	 * @throws SignerException
	 */
	public function __construct($privateKey, $privateKeyPassword, $publicKey)
	{

		if (!file_exists($privateKey)) {
			throw new SignerException("Private key ({$privateKey}) not exists or not readable!");
		}
		if (!is_readable($privateKey)) {
			throw new SignerException("Private key ({$privateKey}) not readable!");
		}

		if (!file_exists($publicKey) || !is_readable($publicKey)) {
			throw new SignerException("Public key ({$publicKey}) not exists or not readable!");
		}

		$this->privateKey = $privateKey;
		$this->privateKeyPassword = $privateKeyPassword;
		$this->publicKey = $publicKey;
	}

	/**
	 * @return resource
	 * @throws SignerException
	 */
	private function getPrivateKeyResource()
	{
		if ($this->privateKeyResource) {
			return $this->privateKeyResource;
		}
		$key = file_get_contents($this->privateKey);
		if (!($this->privateKeyResource = openssl_pkey_get_private($key, $this->privateKeyPassword))) {
			throw new SignerException("'{$this->privateKey}' is not valid PEM private key (or password is incorrect).");
		}
		return $this->privateKeyResource;
	}

	/**
	 * @param array $params
	 * @return string
	 */
	public function sign($params)
	{
		$digestText = implode('|', $params);
		openssl_sign($digestText, $digest, $this->getPrivateKeyResource());
		$digest = base64_encode($digest);
		return $digest;
	}

	/**
	 * @param array $params
	 * @param $digest
	 * @return int
	 * @throws SignerException
	 */
	public function verify($params, $digest)
	{
		$data = implode('|', $params);
		$digest = base64_decode($digest);
		$ok = openssl_verify($data, $digest, $this->getPublicKeyResource());
		if ($ok !== 1) {
			throw new SignerException('Digest is not correct!');
		}
		return $ok;
	}

	/**
	 * @return resource
	 * @throws SignerException
	 */
	private function getPublicKeyResource()
	{
		if ($this->publicKeyResource) {
			return $this->publicKeyResource;
		}
		$fp = fopen($this->publicKey, "r");
		$key = fread($fp, filesize($this->publicKey));
		fclose($fp);
		if (!($this->publicKeyResource = openssl_pkey_get_public($key))) {
			throw new SignerException("'{$this->publicKey}' is not valid PEM public key.");
		}
		return $this->publicKeyResource;
	}
}