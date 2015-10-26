<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 11:42
 */

namespace Pixidos\GPWebPay;

use Pixidos\GPWebPay\Exceptions\GPWebPayResultException;
use Pixidos\GPWebPay\Exceptions\SignerException;
use Pixidos\GPWebPay\Exceptions\GPWebPayException;

/**
 * Class GPWebPayProvider
 * @package Pixidos\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */

class GPWebPayProvider
{

    /**
     * @var GPWebPaySettings $settings
     */
    private $settings;

    /**
     * @var  GPWebPayRequest $request
     */
    private $request;

    /**
     * @var  GPWebPaySigner $signer
     */
    private $signer;

    /**
     * GPWebPayProvider constructor.
     * @param GPWebPaySettings $settings
     */
    public function __construct(GPWebPaySettings $settings)
    {
        $this->settings = $settings;
        $this->signer = new GPWebPaySigner($settings->getPrivateKey(), $settings->getPrivateKeyPassword(), $settings->getPublicKey());
    }


    /**
     * @param Operation $operation
     * @return $this
     */
    public function createRequest(Operation $operation)
    {
        $this->request = new GPWebPayRequest($operation, $this->settings->getMerchantNumber(), $this->settings->getDepositFlag());
        return $this;
    }

    /**
     * @return GPWebPayRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function getRequestUrl()
    {
        $params = $this->request->getParams();
        $this->request->setDigest($this->signer->sign($params));
        \Tracy\Debugger::barDump($this->request->getParams(), 'Params');
        $paymentUrl = $this->settings->getUrl() . '?' . http_build_query($this->request->getParams());

        return $paymentUrl;
    }

    /**
     * @return GPWebPayResponse
     */
    public function createResponse($params )
    {
        $operation = isset ($params ['OPERATION']) ? $params ['OPERATION'] : '';
        $ordernumber = isset ($params ['ORDERNUMBER']) ? $params ['ORDERNUMBER'] : '';
        $merordernum = isset ($params ['MERORDERNUM']) ? $params ['MERORDERNUM'] : NULL;
        $md = isset ($params ['MD']) ? $params['MD'] : NULL;
        $prcode = isset ($params ['PRCODE']) ? $params ['PRCODE'] : '';
        $srcode = isset ($params ['SRCODE']) ? $params ['SRCODE'] : '';
        $resulttext = isset ($params ['RESULTTEXT']) ? $params ['RESULTTEXT'] : '';
        $digest = isset ($params ['DIGEST']) ? $params ['DIGEST'] : '';
        $digest1 = isset ($params ['DIGEST1']) ? $params ['DIGEST1'] : '';

        return new GPWebPayResponse($operation, $ordernumber, $merordernum, $md, $prcode, $srcode, $resulttext, $digest, $digest1);
    }

    /**
     * @param GPWebPayResponse $response
     * @return bool
     * @throws GPWebPayException
     * @throws GPWebPayResultException
     */
    public function verifyPaymentResponse(GPWebPayResponse $response) {
        // verify digest & digest1
        try {
            $responseParams = $response->getParams();
            $this->signer->verify($responseParams, $response->getDigest());
            $responseParams['MERCHANTNUMBER'] = $this->settings->getMerchantNumber();
            $this->signer->verify($responseParams, $response->getDigest1());
        } catch (SignerException $e) {
            throw new GPWebPayException($e->getMessage(), $e->getCode(), $e);
        }
        // verify PRCODE and SRCODE
        if (false !== $response->hasError()) {
            throw new GPWebPayResultException("Response has an error.", $response->getPrcode(), $response->getSrcode());
        }

        return true;
    }
}