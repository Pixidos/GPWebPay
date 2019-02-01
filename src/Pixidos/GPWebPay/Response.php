<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 11:48
 */

namespace Pixidos\GPWebPay;

use Pixidos\GPWebPay\Intefaces\IResponse;

/**
 * Class Response
 * @package Pixidos\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */

class Response implements IResponse
{

	/**
	 * @var array $params
	 */
	private $params;
	/** @var  string */
	private $digest;
	/** @var  string */
	private $digest1;
    
    /**
     * @var string gatewayKey
     */
	private $gatewayKey;

	/**
	 * @param string $operation
	 * @param string $ordernumber
	 * @param string $merordernum
	 * @param string $md
	 * @param int $prcode
	 * @param int $srcode
	 * @param string $resulttext
	 * @param string $digest
	 * @param string $digest1
	 * @param string $gatewayKey
	 */
	public function __construct(
		$operation,
		$ordernumber,
		$merordernum,
		$md,
		$prcode,
		$srcode,
		$resulttext,
		$digest,
		$digest1,
		$gatewayKey
	) {
		$this->params['OPERATION'] = $operation;
		$this->params['ORDERNUMBER'] = $ordernumber;
		if ($merordernum !== NULL) {
			$this->params['MERORDERNUM'] = $merordernum;
		}
		if ($md !== NULL) {
			$this->params['MD'] = $md;
		}
		$this->params['PRCODE'] = (int)$prcode;
		$this->params['SRCODE'] = (int)$srcode;
		if ($resulttext !== NULL) {
			$this->params['RESULTTEXT'] = $resulttext;
		}
		$this->digest = $digest;
		$this->digest1 = $digest1;
		$this->gatewayKey = (string)$gatewayKey;
	}


	/**
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * @return mixed
	 */
	public function getDigest()
	{
		return $this->digest;
	}

	/**
	 * @return bool
	 */
	public function hasError()
	{
		return (bool)$this->params['PRCODE'] || (bool)$this->params['SRCODE'];
	}

	/**
	 * @return string
	 */
	public function getDigest1()
	{
		return $this->digest1;
	}

	/**
	 * @return string | null
	 */
	public function getMerOrderNumber()
	{
		return isset($this->params['MERORDERNUM']) ? $this->params['MERORDERNUM'] : NULL;
	}

	/**
	 * @return string| null
	 */
	public function getMd()
	{
		$explode = explode('|', $this->params['MD'], 2);
		return isset($explode[1]) ? $explode[1] : NULL;

	}

	/**
	 * @return string
	 */
	public function getGatewayKey()
	{
		return $this->gatewayKey;
	}

	/**
	 * @return string
	 */
	public function getOrderNumber()
	{
		return $this->params['ORDERNUMBER'];
	}

	/**
	 * @return int
	 */
	public function getSrcode()
	{
		return $this->params['SRCODE'];
	}

	/**
	 * @return int
	 */
	public function getPrcode()
	{
		return $this->params['PRCODE'];
	}

	/**
	 * @return string|null
	 */
	public function getResultText()
	{
		return isset($this->params['RESULTTEXT']) ? $this->params['RESULTTEXT'] : NULL;
	}

	/**
	 * @return string | null
	 */
	public function getUserParam1()
	{
		return isset($this->params['USERPARAM1']) ? $this->params['USERPARAM1'] : NULL;
	}

	public function setUserParam1($userParam1)
	{
		$this->params['USERPARAM1'] = $userParam1;
	}
}
