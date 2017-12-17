<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 11:13
 */

namespace Pixidos\GPWebPay;

use Pixidos\GPWebPay\Exceptions\InvalidArgumentException;
use Pixidos\GPWebPay\Intefaces\IOperation;
use Pixidos\GPWebPay\Intefaces\IRequest;

/**
 * Class Request
 * @package Pixidos\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */
class Request implements IRequest
{
    public const MERCHANTNUMBER = 'MERCHANTNUMBER';
    public const OPERATION = 'OPERATION';
    public const ORDERNUMBER = 'ORDERNUMBER';
    public const AMOUNT = 'AMOUNT';
    public const CURRENCY = 'CURRENCY';
    public const DEPOSITFLAG = 'DEPOSITFLAG';
    public const MERORDERNUM = 'MERORDERNUM';
    public const URL = 'URL';
    public const DESCRIPTION = 'DESCRIPTION';
    public const MD = 'MD';
    public const USERPARAM_1 = 'USERPARAM1';
    public const FASTPAYID = 'FASTPAYID';
    public const PAYMETHOD = 'PAYMETHOD';
    public const DISABLEPAYMETHOD = 'DISABLEPAYMETHOD';
    public const PAYMETHODS = 'PAYMETHODS';
    public const EMAIL = 'EMAIL';
    public const REFERENCENUMBER = 'REFERENCENUMBER';
    public const ADDINFO = 'ADDINFO';
    public const LANG = 'LANG';
    /**
     *
     * @var array $digestParamsKeys
     */
    private static $digestParamsKeys = [
        self::MERCHANTNUMBER,
        self::OPERATION,
        self::ORDERNUMBER,
        self::AMOUNT,
        self::CURRENCY,
        self::DEPOSITFLAG,
        self::MERORDERNUM,
        self::URL,
        self::DESCRIPTION,
        self::MD,
        self::USERPARAM_1,
        self::FASTPAYID,
        self::PAYMETHOD,
        self::DISABLEPAYMETHOD,
        self::PAYMETHODS,
        self::EMAIL,
        self::REFERENCENUMBER,
        self::ADDINFO,
    ];
    /**
     * @var  IOperation $operation
     */
    private $operation;
    /**
     * @var string|null $url
     */
    private $url;
    /**
     * @var  string $merchantNumber
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
     * @param IOperation $operation
     * @param string     $merchantNumber
     * @param int        $depositFlag
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IOperation $operation, string $merchantNumber, int $depositFlag)
    {
        $this->operation = $operation;
        if (!$this->url = $operation->getResponseUrl()) {
            throw new InvalidArgumentException('Response URL in Operation must by set!');
        }
        
        $this->merchantNumber = $merchantNumber;
        $this->depositFlag = $depositFlag;
        
        $this->setParams();
        
    }
    
    /**
     * Method only for ISinger
     *
     * @param string $digest
     *
     * @internal
     */
    public function setDigest(string $digest): void
    {
        $this->params['DIGEST'] = $digest;
    }
    
    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
    
    /**
     * @return array
     */
    public function getDigestParams(): array
    {
        return array_intersect_key($this->params, array_flip(self::$digestParamsKeys));
    }
    
    /**
     * Sets params to array
     */
    private function setParams()
    {
        $this->params[self::MERCHANTNUMBER] = $this->merchantNumber;
        $this->params[self::OPERATION] = 'CREATE_ORDER';
        $this->params[self::ORDERNUMBER] = $this->operation->getOrderNumber();
        $this->params[self::AMOUNT] = $this->operation->getAmount();
        $this->params[self::CURRENCY] = $this->operation->getCurrency();
        $this->params[self::DEPOSITFLAG] = $this->depositFlag;
        if ($this->operation->getMerOrderNum()) {
            $this->params[self::MERORDERNUM] = $this->operation->getMerOrderNum();
        }
        $this->params[self::URL] = $this->url;
        
        if ($this->operation->getDescription()) {
            $this->params[self::DESCRIPTION] = $this->operation->getDescription();
        }
        if ($this->operation->getMd()) {
            $this->params[self::MD] = $this->operation->getMd();
        }
        if ($this->operation->getLang()) {
            $this->params[self::LANG] = $this->operation->getLang();
        }
        if ($this->operation->getUserParam1()) {
            $this->params[self::USERPARAM_1] = $this->operation->getUserParam1();
        }
        if ($this->operation->getPayMethod()) {
            $this->params[self::PAYMETHOD] = $this->operation->getPayMethod();
        }
        if ($this->operation->getDisablePayMethod()) {
            $this->params[self::DISABLEPAYMETHOD] = $this->operation->getDisablePayMethod();
        }
        if ($this->operation->getPayMethods()) {
            $this->params[self::PAYMETHODS] = $this->operation->getPayMethods();
        }
        if ($this->operation->getEmail()) {
            $this->params[self::EMAIL] = $this->operation->getEmail();
        }
        if ($this->operation->getReferenceNumber()) {
            $this->params[self::REFERENCENUMBER] = $this->operation->getReferenceNumber();
        }
        if ($this->operation->getFastPayId()) {
            $this->params[self::FASTPAYID] = $this->operation->getFastPayId();
        }
    }
    
}
