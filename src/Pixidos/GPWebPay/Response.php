<?php
declare(strict_types=1);

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
    /**
     * @var string digest
     */
	private $digest;
    /**
     * @var string digest1
     */
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
		
		if (null !== $merordernum) {
			$this->params['MERORDERNUM'] = $merordernum;
		}
		
		if (null !== $md) {
			$this->params['MD'] = $md;
		}
		
		$this->params['PRCODE'] = (int)$prcode;
		$this->params['SRCODE'] = (int)$srcode;
		
		if (null !== $resulttext) {
			$this->params['RESULTTEXT'] = $resulttext;
		}
		
		$this->digest = $digest;
		$this->digest1 = $digest1;
		$this->gatewayKey = $gatewayKey;
	}


	/**
	 * @return array
	 */
	public function getParams(): array
	{
		return $this->params;
	}

	/**
	 * @return string
	 */
	public function getDigest(): string
	{
		return $this->digest;
	}

	/**
	 * @return bool
	 */
	public function hasError(): bool
	{
		return (bool)$this->params['PRCODE'] || (bool)$this->params['SRCODE'];
	}

	/**
	 * @return string
	 */
	public function getDigest1(): string
	{
		return $this->digest1;
	}

	/**
	 * @return string|null
	 */
	public function getMerOrderNumber(): ?string
	{
		return $this->params['MERORDERNUM'] ?? null;
	}

	/**
	 * @return string|null
	 */
	public function getMd(): ?string
	{
		$explode = explode('|', $this->params['MD'], 2);
		return $explode[1] ?? null;

	}

	/**
	 * @return string
	 */
	public function getGatewayKey(): string
	{
		return $this->gatewayKey;
	}

	/**
	 * @return string
	 */
	public function getOrderNumber(): string
	{
		return $this->params['ORDERNUMBER'];
	}

	/**
	 * @return int
	 */
	public function getSrcode(): int
	{
		return $this->params['SRCODE'];
	}

	/**
	 * @return int
	 */
	public function getPrcode(): int
	{
		return $this->params['PRCODE'];
	}

	/**
	 * @return string|null
	 */
	public function getResultText(): ?string
	{
		return $this->params['RESULTTEXT'] ?? null;
	}

	/**
	 * @return string|null
	 */
	public function getUserParam1(): ?string
	{
		return $this->params['USERPARAM1'] ?? null;
	}
    
    /**
     * @param string $userParam1
     */
    public function setUserParam1(string $userParam1): void
    {
		$this->params['USERPARAM1'] = $userParam1;
	}
	
	
}
