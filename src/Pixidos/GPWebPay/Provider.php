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

/**
 * Class Provider
 * @package Pixidos\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */
class Provider implements IProvider
{
    
    /**
     * @var Settings $settings
     */
    private $settings;
    
    /**
     * @var  IRequest $request
     */
    private $request;
    
    /**
     * @var  ISigner $signer
     */
    private $signer;
    
    /**
     * Provider constructor.
     *
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
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
        
        $this->signer = new Signer(
            $this->settings->getPrivateKey($operation->getGatewayKey()),
            $this->settings->getPrivateKeyPassword($operation->getGatewayKey()),
            $this->settings->getPublicKey()
        );
        
        return $this;
    }
    
    /**
     * @return IRequest
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * @return ISigner
     */
    public function getSigner()
    {
        return $this->signer;
    }
    
    /**
     *
     * @return string
     */
    public function getRequestUrl()
    {
        $this->request->setDigest($this->signer->sign($this->request->getDigestParams()));
        $paymentUrl = $this->settings->getUrl() . '?' . http_build_query($this->request->getParams());
        
        return $paymentUrl;
    }
    
    /**
     * @param $params
     *
     * @return IResponse
     */
    public function createResponse($params)
    {
        $operation = isset ($params ['OPERATION']) ? $params ['OPERATION'] : '';
        $ordernumber = isset ($params ['ORDERNUMBER']) ? $params ['ORDERNUMBER'] : '';
        $merordernum = isset ($params ['MERORDERNUM']) ? $params ['MERORDERNUM'] : null;
        $md = isset ($params ['MD']) ? $params['MD'] : null;
        $prcode = isset ($params ['PRCODE']) ? $params ['PRCODE'] : '';
        $srcode = isset ($params ['SRCODE']) ? $params ['SRCODE'] : '';
        $resulttext = isset ($params ['RESULTTEXT']) ? $params ['RESULTTEXT'] : null;
        $digest = isset ($params ['DIGEST']) ? $params ['DIGEST'] : '';
        $digest1 = isset ($params ['DIGEST1']) ? $params ['DIGEST1'] : '';
        
        $key = explode('|', $md, 2);
        
        if (empty($key[0])) {
            $gatewayKey = $this->settings->getDefaultGatewayKey();
        } else {
            $gatewayKey = $key[0];
        }
        $response = new Response(
            $operation, $ordernumber, $merordernum, $md, $prcode, $srcode, $resulttext, $digest,
            $digest1, $gatewayKey
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
    public function verifyPaymentResponse(IResponse $response)
    {
        // verify digest & digest1
        try {
            $this->signer = new Signer(
                $this->settings->getPrivateKey($response->getGatewayKey()),
                $this->settings->getPrivateKeyPassword($response->getGatewayKey()),
                $this->settings->getPublicKey()
            );
            
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
