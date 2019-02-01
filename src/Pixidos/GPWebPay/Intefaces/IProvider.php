<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 06.06.2017
 * Time: 22:42
 */

namespace Pixidos\GPWebPay\Intefaces;


use Pixidos\GPWebPay\Exceptions\GPWebPayException;
use Pixidos\GPWebPay\Exceptions\GPWebPayResultException;

interface IProvider
{
    /**
     * @param IOperation $operation
     *
     * @return IProvider
     */
    public function createRequest(IOperation $operation): IProvider;
    
    /**
     * @return IRequest
     */
    public function getRequest(): IRequest;
    
    
    /**
     * @return string
     */
    public function getRequestUrl(): string;
    
    /**
     * @param array $params
     *
     * @return IResponse
     */
    public function createResponse(array $params): IResponse;
    
    /**
     * @param IResponse $response
     *
     * @return bool
     * @throws GPWebPayException
     * @throws GPWebPayResultException
     */
    public function verifyPaymentResponse(IResponse $response): bool;
}
