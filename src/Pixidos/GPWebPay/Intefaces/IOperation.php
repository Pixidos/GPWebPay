<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 06.06.2017
 * Time: 0:37
 */

namespace Pixidos\GPWebPay\Intefaces;

use InvalidArgumentException;

/**
 * Class OperationContract
 * @package Pixidos\GPWebPay\Contracts
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */

interface IOperation
{

	public function getOrderNumber();

	/**
	 * @return int
	 */
	public function getAmount();


	/**
	 * @return int
	 */
	public function getCurrency();

	/**
	 * @return null | string
	 */
	public function getResponseUrl();

	/**
	 * @param string $url max. lenght is 300
	 * @return IOperation
	 */
	public function setResponseUrl($url);


	/**
	 * @return null | string
	 */
	public function getMd();

	/**
	 * @param string $md max. length is 255!
	 * @return IOperation
	 * @throws InvalidArgumentException
	 */
	public function setMd($md);


	/**
	 * @return null | string
	 */
	public function getDescription();

	/**
	 * @param string $description max. length is 255
	 * @return IOperation
	 * @throws InvalidArgumentException
	 */
	public function setDescription($description);

	/**
	 * @return int|null
	 */
	public function getMerOrderNum();

	/**
	 * @param string $merordernum max. length is 30
	 * @return IOperation
	 */
	public function setMerOrderNum($merordernum);

	/**
	 * @return null|string
	 */
	public function getGatewayKey();

	/**
	 *
	 * @param string $lang max. length is 2
	 * @return IOperation
	 */
	public function setLang($lang);

	/**
	 *
	 * @return null|string
	 */
	public function getLang();

	/**
	 * @return string
	 */
	public function getUserParam1();


	/**
	 * @param string $userParam1 max. length is 255
	 * @return IOperation
	 * @throws InvalidArgumentException
	 */
	public function setUserParam1($userParam1);

	/**
	 * @return string
	 */
	public function getPayMethod();

	/**
     * Supported Values:
     * CRD – payment card
     * MCM – MasterCard Mobile
     * MPS – MasterPass
     * BTNCS - PLATBA 24
     * GPAY - Google pay
     *
	 * @param string $payMethod supported val: CRD, MCM, MPS, BTNCS, GPAY
	 * @return IOperation
	 * @throws InvalidArgumentException
	 */
	public function setPayMethod($payMethod);

	/**
	 * @return string
	 */
	public function getDisablePayMethod();

	/**
	 * Supported Values:
	 * CRD – payment card
	 * MCM – MasterCard Mobile
	 * MPS – MasterPass
	 * BTNCS - PLATBA 24
     * GPAY - Google pay
     *
	 * @param string $disablePayMethod supported val: CRD, MCM, MPS, BTNCS, GPAY
	 * @return IOperation
	 * @throws InvalidArgumentException
	 */
	public function setDisablePayMethod($disablePayMethod);
	
	/**
	 * @return array
	 */
	public function getPayMethods();

	/**
	 * List of allowed payment methods.
	 * Supported Values:
	 * CRD – payment card
	 * MCM – MasterCard Mobile
	 * MPS – MasterPass
	 * BTNCS - PLATBA 24
     * GPAY - Google pay
     *
	 * @param array $payMethods supported val: [CRD, MCM, MPS, BTNCS, GPAY]
	 * @return IOperation
	 * @throws InvalidArgumentException
	 */
	public function setPayMethods(array $payMethods);

	/**
	 * @return string
	 */
	public function getEmail();
	
	/**
	 * @param string $value
	 * @return bool
	 */
	public function isEmail($value);

	/**
	 * @param string $email max. lenght is 255
	 * @return IOperation
	 * @throws InvalidArgumentException
	 */
	public function setEmail($email);

	/**
	 * @return string
	 */
	public function getReferenceNumber();
	/**
	 * @param string $referenceNumber max. lenght is 20
	 * @return IOperation
	 * @throws InvalidArgumentException
	 */
	public function setReferenceNumber($referenceNumber);

	/**
	 * @return null
	 */
	public function getFastPayId();

	/**
	 * @param int $fastPayId max. lenght is 15
	 * @return IOperation
	 * @throws InvalidArgumentException
	 */
	public function setFastPayId($fastPayId);
}
