<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 11:13
 */

namespace Pixidos\GPWebPay;
use Pixidos\GPWebPay\Exceptions\GPWebPayException;
use Pixidos\GPWebPay\Exceptions\InvalidArgumentException;

/**
 * Class GPWebPayRequest
 * @package Pixidos\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */

class GPWebPayRequest
{

    /**
     * @var  Operation $operation
     */
    private $operation;

    /**
     * @var string $url
     */
    private $url;

    /**
     * @var  int $merOrderNumber
     */
    private $merOrderNumber;

    /**
     * @var  int $merchantNumber
     */
    private $merchantNumber;

    /**
     * @var  int $depositFlag
     */
    private $depositFlag;

    /**
     * @var array $params
     */
    private $params;

    /**
     * GPWebPayRequest constructor.
     * @param Operation $operation
     * @param $merchantNumber
     * @param int $merOrderNumber
     * @throws GPWebPayException
     */
    public function __construct(Operation $operation, $merchantNumber, $depositFlag, $merOrderNumber = NULL)
    {
        $this->operation = $operation;
        if(! $this->url = $operation->getResponseUrl())
            throw new InvalidArgumentException('Response URL in Operation must by set!');

        $this->merOrderNumber = $merOrderNumber;
        $this->merchantNumber = $merchantNumber;
        $this->depositFlag = $depositFlag;

        $this->setParams();
    }

    private function setParams()
    {
        $this->params['MERCHANTNUMBER'] = $this->merchantNumber;
        $this->params['OPERATION'] = 'CREATE_ORDER';
        $this->params['ORDERNUMBER'] = $this->operation->getOrderNumber();
        $this->params['AMOUNT'] = $this->operation->getAmount();
        $this->params['CURRENCY'] = $this->operation->getCurrency();
        $this->params['DEPOSITFLAG'] = $this->depositFlag;
        $this->params['URL'] = $this->url;

        if ($this->merOrderNumber) {
            $this->params['MERORDERNUM'] = $this->merOrderNumber;
        }
    }

    /**
     * @param string $digest
     */
    public function setDigest($digest)
    {
        $this->params['DIGEST'] = $digest;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

}