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
     * @param Operation $operation
     * @param $merchantNumber
     * @param $depositFlag
     * @throws InvalidArgumentException
     */
    public function __construct(Operation $operation, $merchantNumber, $depositFlag)
    {
        $this->operation = $operation;
        if(! $this->url = $operation->getResponseUrl())
            throw new InvalidArgumentException('Response URL in Operation must by set!');

        $this->merchantNumber = $merchantNumber;
        $this->depositFlag = $depositFlag;

        $this->setParams();

    }

    /**
     * Sets params to array
     */
    private function setParams()
    {
        $this->params['MERCHANTNUMBER'] = $this->merchantNumber;
        $this->params['OPERATION'] = 'CREATE_ORDER';
        $this->params['ORDERNUMBER'] = $this->operation->getOrderNumber();
        $this->params['AMOUNT'] = $this->operation->getAmount();
        $this->params['CURRENCY'] = $this->operation->getCurrency();
        $this->params['DEPOSITFLAG'] = $this->depositFlag;
        if($this->operation->getMerOrderNum())
            $this->params['MERORDERNUM'] = $this->operation->getMerOrderNum();
        $this->params['URL'] = $this->url;

        if($this->operation->getDescription())
        $this->params['DESCRIPTION'] = $this->operation->getDescription();
        if($this->operation->getMd())
        $this->params['MD'] = $this->operation->getMd();

    }

    /**
     * @param string $digest
     * @internal
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