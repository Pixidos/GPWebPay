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
     *
     * @return $this
     */
    public function createRequest(IOperation $operation);
    
    /**
     * @return IRequest
     */
    public function getRequest(): IRequest;
    
    
    /**
     *
     * @return string
     */
    public function getRequestUrl(): string;
    
    /**
     * @param $params
     *
     * @return IResponse
     */
    public function createResponse($params): IResponse;
    
    /**
     * @param IResponse $response
     *
     * @return bool
     */
    public function verifyPaymentResponse(IResponse $response): bool;
}
