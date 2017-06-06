<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 06.06.2017
 * Time: 22:32
 */

namespace Pixidos\GPWebPay\Intefaces;


interface IResponse
{
	/**
	 * @return array
	 */
	public function getParams();

	/**
	 * @return string
	 */
	public function getDigest();

	/**
	 * @return bool
	 */
	public function hasError();

	/**
	 * @return string
	 */
	public function getDigest1();

	/**
	 * @return string | null
	 */
	public function getMerOrderNumber();

	/**
	 * @return string| null
	 */
	public function getMd();

	/**
	 * @return mixed
	 */
	public function getGatewayKey();

	/**
	 * @return string
	 */
	public function getOrderNumber();

	/**
	 * @return int
	 */
	public function getSrcode();

	/**
	 * @return int
	 */
	public function getPrcode();

	/**
	 * @return string|null
	 */
	public function getResultText();

	/**
	 * @return string | null
	 */
	public function getUserParam1();

	public function setUserParam1($userParam1);
}