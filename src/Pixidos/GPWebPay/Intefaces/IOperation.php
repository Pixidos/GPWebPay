<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 06.06.2017
 * Time: 0:37
 */

namespace Pixidos\GPWebPay\Intefaces;

/**
 * Class OperationContract
 * @package Pixidos\GPWebPay\Contracts
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */
interface IOperation
{
    /**
     * @return string
     */
    public function getOrderNumber(): string;
    
    /**
     * @return int
     */
    public function getAmount(): int;
    
    /**
     * @return int
     */
    public function getCurrency(): int;
    
    /**
     * @return null|string
     */
    public function getResponseUrl(): ?string;
    
    /**
     * @param string $url max. lenght is 300
     *
     * @return IOperation
     */
    public function setResponseUrl(string $url): IOperation;
    
    /**
     * @return null|string
     */
    public function getMd(): ?string;
    
    /**
     * @param string $md max. length is 255!
     *
     * @return IOperation
     */
    public function setMd(string $md): IOperation;
    
    
    /**
     * @return null|string
     */
    public function getDescription(): ?string;
    
    /**
     * @param string $description max. length is 255
     *
     * @return IOperation
     */
    public function setDescription(string $description): IOperation;
    
    /**
     * @return null|string
     */
    public function getMerOrderNum(): ?string;
    
    /**
     * @param string $merordernum max. length is 30
     *
     * @return IOperation
     */
    public function setMerOrderNum(string $merordernum): IOperation;
    
    /**
     * @return null|string
     */
    public function getGatewayKey(): ?string;
    
    /**
     *
     * @param string $lang max. length is 2
     *
     * @return IOperation
     */
    public function setLang(string $lang): IOperation;
    
    /**
     *
     * @return null|string
     */
    public function getLang(): ?string;
    
    /**
     * @return string|null
     */
    public function getUserParam1(): ?string;
    
    
    /**
     * @param string $userParam1 max. length is 255
     *
     * @return IOperation
     */
    public function setUserParam1(string $userParam1): IOperation;
    
    /**
     * @return string|null
     */
    public function getPayMethod(): ?string;
    
    /**
     * Supported Values:
     * CRD – payment card
     * MCM – MasterCard Mobile
     * MPS – MasterPass
     * BTNCS - PLATBA 24
     * GPAY - Google Pay
     *
     * @param string $payMethod supported val: CRD, MCM, MPS, BTNCS, GPAY
     *
     * @return IOperation
     */
    public function setPayMethod(string $payMethod): IOperation;
    
    /**
     * @return string|null
     */
    public function getDisablePayMethod(): ?string;
    
    /**
     * Supported Values:
     * CRD – payment card
     * MCM – MasterCard Mobile
     * MPS – MasterPass
     * BTNCS - PLATBA 24
     * GPAY - Google Pay
     *
     * @param string $disablePayMethod supported val: CRD, MCM, MPS, BTNCS, GPAY
     *
     * @return IOperation
     */
    public function setDisablePayMethod(string $disablePayMethod): IOperation;
    
    /**
     * @return string|null
     */
    public function getPayMethods(): ?string;
    
    /**
     * List of allowed payment methods.
     * Supported Values:
     * CRD – payment card
     * MCM – MasterCard Mobile
     * MPS – MasterPass
     * BTNCS - PLATBA 24
     * GPAY - Google Pay
     *
     * @param array $payMethods supported val: [CRD, MCM, MPS, BTNCS, GPAY]
     *
     * @return IOperation
     */
    public function setPayMethods($payMethods): IOperation;
    
    /**
     * @return string|null
     */
    public function getEmail(): ?string;
    
    /**
     * @param string $value
     *
     * @return bool
     */
    public function isEmail(string $value): bool;
    
    /**
     * @param string $email max. lenght is 255
     *
     * @return IOperation
     */
    public function setEmail(string $email): IOperation;
    
    /**
     * @return string|null
     */
    public function getReferenceNumber(): ?string;
    
    /**
     * @param string $referenceNumber max. lenght is 20
     *
     * @return IOperation
     */
    public function setReferenceNumber(string $referenceNumber): IOperation;
    
    /**
     * @return int|float|string
     */
    public function getFastPayId();
    
    /**
     * @param int|float|string $fastPayId max. lenght is 15 and can contain only numbers without 0 on first position
     *
     * @return IOperation
     */
    public function setFastPayId($fastPayId): IOperation;
}
