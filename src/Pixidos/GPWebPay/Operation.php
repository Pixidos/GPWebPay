<?php
declare(strict_types=1);

namespace Pixidos\GPWebPay;

use Pixidos\GPWebPay\Exceptions\InvalidArgumentException;
use Pixidos\GPWebPay\Intefaces\IOperation;

/**
 * Class Operation
 * @package Pixidos\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */
class Operation implements IOperation
{
    
    public const EUR = 978;
    public const CZK = 203;
    public const PAYMENT_CARD = 'CRD';
    public const PAYMENT_MASTERCARD_MOBILE = 'MCM';
    public const PAYMENT_MASTERPASS = 'MPS';
    public const PAYMENT_PLATBA24 = 'BTNCS';
    public const PAYMENT_GOOGLE_PAY = 'GPAY';
    
    private static $payMethodSupportedVal = [
        self::PAYMENT_CARD,
        self::PAYMENT_MASTERCARD_MOBILE,
        self::PAYMENT_MASTERPASS,
        self::PAYMENT_PLATBA24,
        self::PAYMENT_GOOGLE_PAY,
    ];
    
    /**
     * @var string $orderNumber
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
     * @var string $description
     */
    private $description;
    /**
     * @var string|null $md
     */
    private $md;
    /**
     * @var string $merordernum
     */
    private $merordernum;
    /**
     * @var string|null $responseUrl
     */
    private $responseUrl;
    /**
     * @var string|null $gatewayKey
     */
    private $gatewayKey;
    /**
     * @var string $lang
     */
    private $lang;
    /**
     * @var string $userParam1
     */
    private $userParam1;
    /**
     * @var string $payMethod
     */
    private $payMethod;
    /**
     * @var string $disablePayMethod
     */
    private $disablePayMethod;
    /**
     * @var string $payMethods
     */
    private $payMethods;
    /**
     * @var string $email
     */
    private $email;
    /**
     * @var string $referenceNumber
     */
    private $referenceNumber;
    
    /**
     * @var int|float|string fastPayId
     */
    private $fastPayId;
    
    
    /**
     * Operation constructor.
     *
     * @param string      $orderNumber max. length is 15
     * @param int|float   $amount
     * @param int         $currency max. length is 3
     * @param null|string $gatewayKey
     * @param null|string $responseUrl
     * @param bool        $converToPennies
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $orderNumber,
        $amount,
        int $currency,
        ?string $gatewayKey = null,
        ?string $responseUrl = null,
        bool $converToPennies = true
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
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }
    
    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }
    
    /**
     * @return int
     */
    public function getCurrency(): int
    {
        return $this->currency;
    }
    
    /**
     * @return null|string
     */
    public function getResponseUrl(): ?string
    {
        return $this->responseUrl ?? null;
    }
    
    /**
     * @param string $url max. lenght is 300
     *
     * @return IOperation
     * @throws InvalidArgumentException
     */
    public function setResponseUrl(string $url): IOperation
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('URL is Invalid');
        }
        
        $strlen = \strlen($url);
        if ($strlen > 300) {
            throw new InvalidArgumentException(sprintf('URL max. length is 300! "%s" given', $strlen));
        }
        
        $this->responseUrl = $url;
        
        return $this;
    }
    
    /**
     * @return null|string
     */
    public function getMd(): ?string
    {
        return $this->md ?? null;
    }
    
    /**
     * @param string $md max. length is 250!
     *
     * @return IOperation
     * @throws InvalidArgumentException
     */
    public function setMd(string $md): IOperation
    {
        $strlen = \strlen($md);
        if ((\strlen($this->md) + $strlen) > 250) {
            throw new InvalidArgumentException(sprintf('MD max. length is 250! "%s" given', $strlen));
        }
        
        $this->md .= '|' . $md;
        
        return $this;
    }
    
    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description ?? null;
    }
    
    /**
     * @param string $description max. length is 255
     *
     * @return IOperation
     * @throws InvalidArgumentException
     */
    public function setDescription(string $description): IOperation
    {
        $strlen = \strlen($description);
        if ($strlen > 255) {
            throw new InvalidArgumentException(sprintf('DESCRIPTION max. length is 255! "%s" given', $strlen));
        }
        
        $this->description = $description;
        
        return $this;
    }
    
    /**
     * @return null|string
     */
    public function getMerOrderNum(): ?string
    {
        return $this->merordernum ?? null;
    }
    
    /**
     * @param string $merordernum max. length is 30
     *
     * @return IOperation
     * @throws InvalidArgumentException
     */
    public function setMerOrderNum(string $merordernum): IOperation
    {
        $strlen = \strlen($merordernum);
        if ($strlen > 30) {
            throw new InvalidArgumentException(sprintf('MERORDERNUM max. length is 30! "%s" given', $strlen));
        }
        $this->merordernum = $merordernum;
        
        return $this;
    }
    
    /**
     * @return null|string
     */
    public function getGatewayKey(): ?string
    {
        return $this->gatewayKey;
    }
    
    /**
     * @return null|string
     */
    public function getLang(): ?string
    {
        return $this->lang;
    }
    
    /**
     *
     * @param string $lang max. length is 2
     *
     * @return IOperation
     * @throws InvalidArgumentException
     */
    public function setLang(string $lang): IOperation
    {
        $strlen = \strlen($lang);
        if ($strlen > 2) {
            throw new InvalidArgumentException(sprintf('LANG max. length is 2! "%s" given', $strlen));
        }
        $this->lang = $lang;
        
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getUserParam1(): ?string
    {
        return $this->userParam1;
    }
    
    /**
     * @param string $userParam1 max. length is 255
     *
     * @return IOperation
     * @throws InvalidArgumentException
     */
    public function setUserParam1(string $userParam1): IOperation
    {
        $strlen = \strlen($userParam1);
        if ($strlen > 255) {
            throw new InvalidArgumentException(sprintf('USERPARAM1 max. length is 255! "%s" given', $strlen));
        }
        $this->userParam1 = $userParam1;
        
        return $this;
    }
    
    /**
     * @return null|string
     */
    public function getPayMethod(): ?string
    {
        return $this->payMethod;
    }
    
    /**
     * @param string $payMethod supported val: Operation::PAYMENT_xxx
     *
     * @return IOperation
     * @throws InvalidArgumentException
     */
    public function setPayMethod(string $payMethod): IOperation
    {
        
        $strlen = \strlen($payMethod);
        if ($strlen > 255) {
            throw new InvalidArgumentException(sprintf('PAYMETHOD max. length is 255! "%s" given', $strlen));
        }
        
        $payMethodUpper = strtoupper($payMethod);
        if (!\in_array($payMethodUpper, self::$payMethodSupportedVal, true)) {
            throw new InvalidArgumentException(
                sprintf('PAYMETHOD supported values: "%s" given: "%s"', implode(', ', self::$payMethodSupportedVal), $payMethodUpper)
            );
        }
        
        $this->payMethod = $payMethodUpper;
        
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getDisablePayMethod(): ?string
    {
        return $this->disablePayMethod;
    }
    
    /**
     * Supported Values:
     * CRD – payment card
     * MCM – MasterCard Mobile
     * MPS – MasterPass
     * BTNCS - PLATBA 24
     * GPAY - Google Pay
     *
     * @param string $disablePayMethod supported val: Operation::PAYMENT_xxx
     *
     * @return IOperation
     * @throws InvalidArgumentException
     */
    public function setDisablePayMethod(string $disablePayMethod): IOperation
    {
        
        $strlen = \strlen($disablePayMethod);
        if ($strlen > 255) {
            throw new InvalidArgumentException(sprintf('DISABLEPAYMETHOD max. length is 255! "%s" given', $strlen));
        }
        
        $disblePayMethodUpper = strtoupper($disablePayMethod);
        if (!\in_array($disblePayMethodUpper, self::$payMethodSupportedVal, true)) {
            $implode = implode(', ', self::$payMethodSupportedVal);
            throw new InvalidArgumentException(
                sprintf('DISABLEPAYMETHOD supported values: "%s" given: "%s"', $implode, $disblePayMethodUpper)
            );
        }
        
        $this->disablePayMethod = $disablePayMethod;
        
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getPayMethods(): ?string
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
     * GPAY - Google Pay
     *
     * @param array|string $payMethods supported val: [CRD, MCM, MPS, BTNCS, GPAY]
     *
     * @return IOperation
     * @throws InvalidArgumentException
     */
    public function setPayMethods($payMethods): IOperation
    {
        if (!\is_array($payMethods)) {
            trigger_error(
                'Use array instead of string or ' . static::class . '::setPayMethod(). string support will be removed in next version', E_USER_DEPRECATED
            );
            $payMethods = [$payMethods];
        }
        
        $suppValImplode = implode(', ', self::$payMethodSupportedVal);
        
        foreach ($payMethods as $key => $val) {
            $upperVal = strtoupper($val);
            
            if (!\in_array($upperVal, self::$payMethodSupportedVal, true)) {
                throw new InvalidArgumentException(sprintf('PAYMETHODS supported values: "%s" given: "%s"', $suppValImplode, $upperVal));
            }
            $payMethods[$key] = $upperVal;
        }
        
        $str = implode(',', $payMethods);
        
        $strlen = \strlen($str);
        if ($strlen > 255) {
            throw new InvalidArgumentException(sprintf('PAYMETHODS max. length is 255! "%s" given', $strlen));
        }
        
        $this->payMethods = $str;
        
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }
    
    /**
     * @param string $email max. lenght is 255
     *
     * @return IOperation
     * @throws InvalidArgumentException
     */
    public function setEmail(string $email): IOperation
    {
        if (!$this->isEmail($email)) {
            throw new InvalidArgumentException(sprintf('EMAIL is not valid! "%s" given', $email));
        }
        $strlen = \strlen($email);
        if ($strlen > 255) {
            throw new InvalidArgumentException(sprintf('EMAIL max. length is 255! "%s" given', $strlen));
        }
        
        $this->email = $email;
        
        return $this;
    }
    
    /**
     * @param string $value
     *
     * @return bool
     */
    public function isEmail(string $value): bool
    {
        $atom = "[-a-z0-9!#$%&'*+/=?^_`{|}~]"; // RFC 5322 unquoted characters in local-part
        $alpha = "a-z\x80-\xFF"; // superset of IDN
        
        return (bool)preg_match(
            "(^
			(\"([ !#-[\\]-~]*|\\\\[ -~])+\"|$atom+(\\.$atom+)*)  # quoted or unquoted
			@
			([0-9$alpha]([-0-9$alpha]{0,61}[0-9$alpha])?\\.)+    # domain - RFC 1034
			[$alpha]([-0-9$alpha]{0,17}[$alpha])?                # top domain
		\\z)ix", $value
        );
    }
    
    /**
     * @return string|null
     */
    public function getReferenceNumber(): ?string
    {
        return $this->referenceNumber;
    }
    
    /**
     * @param string $referenceNumber max. lenght is 20
     *
     * @return IOperation
     * @throws InvalidArgumentException
     */
    public function setReferenceNumber(string $referenceNumber): IOperation
    {
        $strlen = \strlen($referenceNumber);
        if ($strlen > 20) {
            throw new InvalidArgumentException(sprintf('REFERENCENUMBER max. length is 20! "%s" given', $strlen));
        }
        $this->referenceNumber = $referenceNumber;
        
        return $this;
    }
    
    /**
     * @return int|float|string
     */
    public function getFastPayId()
    {
        return $this->fastPayId;
    }
    
    /**
     * @param int|float|string $fastPayId max. lenght is 15 and can contain only numbers
     *
     * @return IOperation
     * @throws InvalidArgumentException
     */
    public function setFastPayId($fastPayId): IOperation
    {
        $this->isNumeric($fastPayId, 15, 'FASTPAYID');
        
        $this->fastPayId = $fastPayId;
        
        return $this;
    }
    
    /**
     * @param string $orderNumber max. lenght is 15 and can contain only numbers without 0 on first position
     *
     * @throws InvalidArgumentException
     */
    private function setOrderNumber(string $orderNumber)
    {
        $this->isNumeric($orderNumber, 15, 'ORDERNUMBER');
        
        $this->orderNumber = $orderNumber;
    }
    
    /**
     * @param int|float $amount
     * @param bool      $converToPennies
     *
     * @return IOperation
     * @throws InvalidArgumentException
     */
    private function setAmount($amount, bool $converToPennies = true): IOperation
    {
        if (!\is_int($amount) && !\is_float($amount)) {
            throw new InvalidArgumentException(sprintf('AMOUNT must be type of INT or FLOAT ! "%s" given', \gettype($amount)));
        }
        // prevod na halere/centy
        if ($converToPennies) {
            $amount *= 100;
        }
        $this->amount = (int)$amount;
        
        return $this;
    }
    
    /**
     * @param int $currency max lenght is 3.
     *
     * @throws InvalidArgumentException
     */
    private function setCurrency(int $currency)
    {
        $strlen = \strlen((string)$currency);
        if ($strlen > 3) {
            throw new InvalidArgumentException(sprintf('CURRENCY code max. length is 3! "%s" given', $strlen));
        }
        
        $this->currency = $currency;
    }
    
    /**
     * @param int|float|string $value
     *
     * @param int              $length
     * @param string           $name
     *
     * @throws InvalidArgumentException
     */
    private function isNumeric($value, int $length, string $name): void
    {
        $strlen = \strlen((string)$value);
        if ($strlen > $length) {
            throw new InvalidArgumentException(sprintf('%s max. length is %s! "%s" given', $name, $length, $strlen));
        }
        
        if (!preg_match('#^[1-9]\d*$#', (string)$value)) {
            throw new InvalidArgumentException(sprintf('%s must be number "%s" given', $name, $value));
        }
    }
    
}
