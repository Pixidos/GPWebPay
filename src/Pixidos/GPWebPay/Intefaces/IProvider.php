<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 06.06.2017
 * Time: 22:42
 */

namespace Pixidos\GPWebPay\Intefaces;


interface IProvider
{

	/**
	 * @param IOperation $operation
	 * @return $this
	 * @throws \Pixidos\GPWebPay\Exceptions\SignerException
	 * @throws \Pixidos\GPWebPay\Exceptions\InvalidArgumentException
	 */
	public function createRequest(IOperation $operation);

	/**
	 * @return IRequest
	 */
	public function getRequest();

	/**
	 * @return ISigner
	 */
	public function getSigner();

	/**
	 *
	 * @return string
	 */
	public function getRequestUrl();

	/**
	 * @param $params
	 * @return IResponse
	 */
	public function createResponse($params);

	/**
	 * @param IResponse $response
	 * @return bool
	 * @throws \Pixidos\GPWebPay\Exceptions\GPWebPayException
	 * @throws \Pixidos\GPWebPay\Exceptions\GPWebPayResultException
	 */
	public function verifyPaymentResponse(IResponse $response);
}