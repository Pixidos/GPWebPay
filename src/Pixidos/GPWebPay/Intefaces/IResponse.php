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
    public function getParams(): array;
    
    /**
     * @return string
     */
    public function getDigest(): string;
    
    /**
     * @return bool
     */
    public function hasError(): bool;
    
    /**
     * @return string
     */
    public function getDigest1(): string;
    
    /**
     * @return string|null
     */
    public function getMerOrderNumber(): ?string;
    
    /**
     * @return string|null
     */
    public function getMd(): ?string;
    
    /**
     * @return string
     */
    public function getGatewayKey(): string;
    
    /**
     * @return string
     */
    public function getOrderNumber(): string;
    
    /**
     * @return int
     */
    public function getSrcode(): int;
    
    /**
     * @return int
     */
    public function getPrcode(): int;
    
    /**
     * @return string|null
     */
    public function getResultText(): ?string;
    
    /**
     * @return string|null
     */
    public function getUserParam1(): ?string;
    
    /**
     * @param string $userParam1
     */
    public function setUserParam1(string $userParam1);
}
