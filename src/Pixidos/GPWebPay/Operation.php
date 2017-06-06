<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 11:18
 */

namespace Pixidos\GPWebPay;

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
	 * @var string $lang
	 */
	private $lang;

	/**
	 * @var  string $userParam1
	 */
	private $userParam1 = NULL;
	/**
	 * @var  string $payMethod
	 */
	private $payMethod = NULL;
	/**
	 * @var  string $disablePayMethod
	 */
	private $disablePayMethod = NULL;
	/**
	 * @var  array $payMethods
	 */
	private $payMethods = [];
	/**
	 * @var  string $email
	 */
	private $email = NULL;
	/**
	 * @var  string $referenceNumber
	 */
	private $referenceNumber = NULL;

	/**
	 * @var int fastPayId
	 */
	private $fastPayId = NULL;

	private $payMethodSupportedVal = [
		'CRD',
		'MCM',
		'MPS',
		'BTNCS',
	];


	/**
	 * Operation constructor.
	 * @param string $orderNumber max. length is 15
	 * @param int $amount
	 * @param int $currency max. length is 3
	 * @param null $gatewayKey
	 * @param null $responseUrl
	 * @param bool $converToPennies
	 * @throws InvalidArgumentException
	 */
	public function __construct(
		$orderNumber,
		$amount,
		$currency,
		$gatewayKey = NULL,
		$responseUrl = NULL,
		$converToPennies = TRUE
	) {

		$this->setOrderNumber($orderNumber);
		$this->setAmount($amount, $converToPennies);
		$this->setCurrency($currency);

		$this->gatewayKey = $gatewayKey;

		$this->md = $gatewayKey;

		if ($responseUrl) {
			$this->setResponseUrl($responseUrl);
		}


	}

	/**
	 * @param int $orderNumber
	 * @throws InvalidArgumentException
	 */
	private function setOrderNumber($orderNumber)
	{
		if (strlen($orderNumber) > 15) {
			throw new InvalidArgumentException('ORDERNUMBER max. length is 15! ' . strlen($orderNumber) . ' given');
		}
		if (!is_numeric($orderNumber) || floor($orderNumber) != $orderNumber) {
			throw new InvalidArgumentException('ORDERNUMBER must by type of numeric ' . gettype($orderNumber) . ' given');
		}
		$this->orderNumber = $orderNumber;
	}

	/**
	 * @param int | float $amount
	 * @param bool $converToPennies
	 * @return $this
	 * @throws InvalidArgumentException
	 */
	private function setAmount($amount, $converToPennies = TRUE)
	{
		if (!is_int($amount) && !is_float($amount)) {
			throw new InvalidArgumentException('AMOUNT must by type of INT or FLOAT !' . gettype($amount) . ' given');
		}

		// prevod na halere/centy
		if ($converToPennies) {
			$amount *= 100;
		}
		$this->amount = (int)$amount;
		return $this;
	}

	/**
	 * @param $currency
	 * @throws InvalidArgumentException
	 */
	private function setCurrency($currency)
	{
		if (!is_int($currency)) {
			throw new InvalidArgumentException('CURRENCY must by INT ! ' . gettype($currency) . ' given');
		}
		if (strlen($currency) > 3) {
			throw new InvalidArgumentException('CURRENCY code max. length is 3! ' . strlen($currency) . ' given');
		}

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
	 * @param string $url max. lenght is 300
	 * @return $this
	 * @throws InvalidArgumentException
	 */
	public function setResponseUrl($url)
	{
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			throw new InvalidArgumentException('URL is Invalid');
		}

		if (strlen($url) > 300) {
			throw new InvalidArgumentException('URL max. length is 300! ' . strlen($url) . ' given');
		}

		$this->responseUrl = $url;

		return $this;
	}

	/**
	 * @return null | string
	 */
	public function getMd()
	{
		return $this->md ?: NULL;
	}

	/**
	 * @param string $md max. length is 255!
	 * @return $this
	 * @throws InvalidArgumentException
	 */
	public function setMd($md)
	{
		if ((strlen((string)$this->md) + strlen($md)) > 250) {
			throw new InvalidArgumentException('MD max. length is 250! ' . strlen($md) . ' given');
		}

		$this->md .= '|' . $md;

		return $this;
	}

	/**
	 * @return null | string
	 */
	public function getDescription()
	{
		return $this->description ?: NULL;
	}

	/**
	 * @param string $description max. length is 255
	 * @return $this
	 * @throws InvalidArgumentException
	 */
	public function setDescription($description)
	{
		if (strlen($description) > 255) {
			throw new InvalidArgumentException('DESCRIPTION max. length is 255! ' . strlen($description) . ' given');
		}

		$this->description = $description;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getMerOrderNum()
	{
		return $this->merordernum ?: NULL;
	}

	/**
	 * @param string $merordernum max. length is 30
	 * @return $this
	 * @throws InvalidArgumentException
	 */
	public function setMerOrderNum($merordernum)
	{
		if (strlen($merordernum) > 30) {
			throw new InvalidArgumentException('MERORDERNUM max. length is 30! ' . strlen($merordernum) . ' given');
		}
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

	/**
	 *
	 * @param string $lang max. length is 2
	 * @return \Pixidos\GPWebPay\Operation
	 * @throws InvalidArgumentException
	 */
	public function setLang($lang)
	{
		if (strlen($lang) > 2) {
			throw new InvalidArgumentException('LANG max. length is 2! ' . strlen($lang) . ' given');
		}
		$this->lang = (string)$lang;

		return $this;
	}

	/**
	 *
	 * @return null|string
	 */
	public function getLang()
	{
		return $this->lang;
	}

	/**
	 * @return string
	 */
	public function getUserParam1()
	{
		return $this->userParam1;
	}

	/**
	 * @param string $userParam1 max. length is 255
	 * @return Operation
	 * @throws InvalidArgumentException
	 */
	public function setUserParam1($userParam1)
	{
		if (strlen((string)$userParam1) > 255) {
			throw new InvalidArgumentException('USERPARAM1 max. length is 255! ' . strlen((string)$userParam1) . ' given');
		}
		$this->userParam1 = $userParam1;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPayMethod()
	{
		return $this->payMethod;
	}

	/**
	 * @param string $payMethod supported val: CRD – payment card | MCM – MasterCard Mobile | MPS – MasterPass | BTNCS - PLATBA 24
	 * @return Operation
	 * @throws InvalidArgumentException
	 */
	public function setPayMethod($payMethod)
	{

		if (strlen((string)$payMethod) > 255) {
			throw new InvalidArgumentException('PAYMETHOD max. length is 255! ' . strlen((string)$payMethod) . ' given');
		}

		$payMethod = strtoupper($payMethod);
		if (!in_array($payMethod, $this->payMethodSupportedVal)) {
			throw new InvalidArgumentException('PAYMETHOD supported values: '
				. implode(', ', $this->payMethodSupportedVal) . ' given: ' . strtoupper($payMethod));
		}

		$this->payMethod = strtoupper($payMethod);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDisablePayMethod()
	{
		return $this->disablePayMethod;
	}

	/**
	 * Supported Values:
	 * CRD – payment card
	 * MCM – MasterCard Mobile
	 * MPS – MasterPass
	 * BTNCS - PLATBA 24
	 * @param string $disablePayMethod supported val: CRD, MCM, MPS, BTNCS
	 * @return Operation
	 * @throws InvalidArgumentException
	 */
	public function setDisablePayMethod($disablePayMethod)
	{

		if (strlen((string)$disablePayMethod) > 255) {
			throw new InvalidArgumentException('DISABLEPAYMETHOD max. length is 255! ' . strlen((string)$disablePayMethod) . ' given');
		}

		if (!in_array(strtoupper($disablePayMethod), $this->payMethodSupportedVal, TRUE)) {
			throw new InvalidArgumentException('DISABLEPAYMETHOD supported values: '
				. implode(', ', $this->payMethodSupportedVal) . ' given: ' . strtoupper($disablePayMethod));
		}

		$this->disablePayMethod = $disablePayMethod;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPayMethods()
	{
		return $this->payMethods;
	}

	/**
	 * List of allowed payment methods.
	 * Supported Values:
	 * CRD – payment card
	 * MCM – MasterCard Mobile
	 * MPS – MasterPass
	 * BTNCS - PLATBA 24
	 * @param array $payMethods supported val: [CRD, MCM, MPS, BTNCS]
	 * @return Operation
	 * @throws InvalidArgumentException
	 */
	public function setPayMethods($payMethods)
	{
		if(! is_array($payMethods)){
			$payMethods = [$payMethods];
		}

		$suppValImplode = implode(', ', $this->payMethodSupportedVal);

		foreach ($payMethods as $key => $val) {
			$val = strtoupper($val);
			$payMethods[$key] = $val;
			if (!in_array($val, $this->payMethodSupportedVal)) {
				throw new InvalidArgumentException('PAYMETHODS supported values: '
					. $suppValImplode . ' given: ' . strtoupper($val));
			}
		}

		$str = implode(",", $payMethods);
		if (strlen($str) > 255) {
			throw new InvalidArgumentException('PAYMETHODS max. length is 255! ' . strlen($str) . ' given');
		}

		$this->payMethods = $str;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	public function isEmail($value)
	{
		$atom = "[-a-z0-9!#$%&'*+/=?^_`{|}~]"; // RFC 5322 unquoted characters in local-part
		$alpha = "a-z\x80-\xFF"; // superset of IDN
		return (bool)preg_match("(^
			(\"([ !#-[\\]-~]*|\\\\[ -~])+\"|$atom+(\\.$atom+)*)  # quoted or unquoted
			@
			([0-9$alpha]([-0-9$alpha]{0,61}[0-9$alpha])?\\.)+    # domain - RFC 1034
			[$alpha]([-0-9$alpha]{0,17}[$alpha])?                # top domain
		\\z)ix", $value);
	}

	/**
	 * @param string $email max. lenght is 255
	 * @return Operation
	 * @throws InvalidArgumentException
	 */
	public function setEmail($email)
	{
		if (!$this->isEmail($email)) {
			throw new InvalidArgumentException('EMAIL is not valid! ' . $email . ' given');
		}
		if (strlen((string)$email) > 255) {
			throw new InvalidArgumentException('EMAIL max. length is 255! ' . strlen($email) . ' given');
		}

		$this->email = $email;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getReferenceNumber()
	{
		return $this->referenceNumber;
	}

	/**
	 * @param string $referenceNumber max. lenght is 20
	 * @return Operation
	 * @throws InvalidArgumentException
	 */
	public function setReferenceNumber($referenceNumber)
	{
		if (strlen((string)$referenceNumber) > 20) {
			throw new InvalidArgumentException('REFERENCENUMBER max. length is 20! ' . strlen($referenceNumber) . ' given');
		}
		$this->referenceNumber = $referenceNumber;
		return $this;
	}

	/**
	 * @return null
	 */
	public function getFastPayId()
	{
		return $this->fastPayId;
	}

	/**
	 * @param int $fastPayId max. lenght is 15
	 * @return Operation
	 * @throws InvalidArgumentException
	 */
	public function setFastPayId($fastPayId)
	{
		if (strlen((string)$fastPayId) > 15) {
			throw new InvalidArgumentException('FASTPAYID max. length is 15! ' . strlen($fastPayId) . ' given');
		}

		if (!is_int($fastPayId)) {
			throw new InvalidArgumentException('FASTPAYID must by numeric! ' . gettype($fastPayId) . ' given');
		}

		$this->fastPayId = $fastPayId;
		return $this;
	}

}
