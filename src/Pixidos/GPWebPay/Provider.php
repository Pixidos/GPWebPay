<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 11:42
 */

namespace Pixidos\GPWebPay;

use Pixidos\GPWebPay\Exceptions\GPWebPayException;
use Pixidos\GPWebPay\Exceptions\GPWebPayResultException;
use Pixidos\GPWebPay\Exceptions\SignerException;
use Pixidos\GPWebPay\Intefaces\IOperation;
use Pixidos\GPWebPay\Intefaces\IProvider;
use Pixidos\GPWebPay\Intefaces\IRequest;
use Pixidos\GPWebPay\Intefaces\IResponse;
use Pixidos\GPWebPay\Intefaces\ISigner;
use Pixidos\GPWebPay\Intefaces\ISignerFactory;

/**
 * Class Provider
 * @package Pixidos\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */
class Provider implements IProvider
{
    
    /**
     * @var ISignerFactory signerFactory
     */
    private $signerFactory;
    
    /**
     * @var  IRequest $request
     */
    private $request;
    
    /**
     * @var  ISigner $signer
     */
    private $signer;
    /**
     * @var Settings settings
     */
    private $settings;
    
    /**
     * Provider constructor.
     *
     * @param Settings       $settings
     * @param ISignerFactory $signerFactory
     */
    public function __construct(Settings $settings, ISignerFactory $signerFactory)
    {
        $this->signerFactory = $signerFactory;
        $this->settings = $settings;
    }
    
    
    /**
     * @param IOperation $operation
     *
     * @return $this
     * @throws \Pixidos\GPWebPay\Exceptions\SignerException
     * @throws \Pixidos\GPWebPay\Exceptions\InvalidArgumentException
     */
    public function createRequest(IOperation $operation)
    {
        $this->request = new Request(
            $operation,
            $this->settings->getMerchantNumber($operation->getGatewayKey()),
            $this->settings->getDepositFlag()
        );
        
        $this->signer = $this->signerFactory->create($operation->getGatewayKey());
        
        return $this;
    }
    
    /**
     * @return IRequest
     */
    public function getRequest(): IRequest
    {
        return $this->request;
    }
    
    /**
     *
     * @return string
     */
    public function getRequestUrl(): string
    {
        $this->request->setDigest($this->signer->sign($this->request->getDigestParams()));
        $paymentUrl = $this->settings->getUrl() . '?' . http_build_query($this->request->getParams());
        
        return $paymentUrl;
    }
    
    /**
     * @param array $params
     *
     * @return IResponse
     */
    public function createResponse(array $params): IResponse
    {
        $operation = $params['OPERATION'] ?: '';
        $ordernumber = $params['ORDERNUMBER'] ?: '';
        $merordernum = $params['MERORDERNUM'] ?: null;
        $md = $params['MD'] ?: null;
        $prcode = (int) $params['PRCODE'] ?: 1000;
        $srcode = (int) $params['SRCODE'] ?: 1000;
        $resulttext = $params['RESULTTEXT'] ?: null;
        $digest = $params['DIGEST'] ?: '';
        $digest1 = $params['DIGEST1'] ?: '';
        
        $key = explode('|', $md, 2);
        
        $gatewayKey = $key[0] ?: $this->settings->getDefaultGatewayKey();
        $response = new Response(
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
        );
        
        if (isset($params['USERPARAM1'])) {
            $response->setUserParam1($params['USERPARAM1']);
        }
        
        return $response;
    }
    
    /**
     * @param IResponse $response
     *
     * @return bool
     * @throws GPWebPayException
     * @throws GPWebPayResultException
     */
    public function verifyPaymentResponse(IResponse $response): bool
    {
        // verify digest & digest1
        try {
            $this->signer = $this->signerFactory->create($response->getGatewayKey());
            
            $responseParams = $response->getParams();
            $this->signer->verify($responseParams, $response->getDigest());
            $responseParams['MERCHANTNUMBER'] = $this->settings->getMerchantNumber($response->getGatewayKey());
            $this->signer->verify($responseParams, $response->getDigest1());
        } catch (SignerException $e) {
            throw new GPWebPayException($e->getMessage(), $e->getCode(), $e);
        }
        // verify PRCODE and SRCODE
        if (false !== $response->hasError()) {
            throw new GPWebPayResultException(
                'Response has an error.', $response->getPrcode(), $response->getSrcode(),
                $response->getResultText()
            );
        }
        
        return true;
    }
    
}
