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
 * Class Provider
 * @package Pixidos\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */

class Provider
{

    /**
     * @var Settings $settings
     */
    private $settings;

    /**
     * @var  Request $request
     */
    private $request;

    /**
     * @var  Signer $signer
     */
    private $signer;

    /**
     * Provider constructor.
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        $this->signer = new Signer($settings->getPrivateKey(), $settings->getPrivateKeyPassword(), $settings->getPublicKey());
    }


    /**
     * @param Operation $operation
     * @return $this
     */
    public function createRequest(Operation $operation)
    {
        $this->request = new Request($operation, $this->settings->getMerchantNumber(), $this->settings->getDepositFlag());
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Signer
     */
    public function getSigner()
    {
        return $this->signer;
    }

    public function getRequestUrl()
    {
        $params = $this->request->getParams();
        $this->request->setDigest($this->signer->sign($params));
        $paymentUrl = $this->settings->getUrl() . '?' . http_build_query($this->request->getParams());

        return $paymentUrl;
    }

    /**
     * @return Response
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

        return new Response($operation, $ordernumber, $merordernum, $md, $prcode, $srcode, $resulttext, $digest, $digest1);
    }

    /**
     * @param Response $response
     * @return bool
     * @throws GPWebPayException
     * @throws GPWebPayResultException
     */
    public function verifyPaymentResponse(Response $response) {
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
            throw new GPWebPayResultException("Response has an error.", $response->getPrcode(), $response->getSrcode(), $response->getResultText());
        }

        return true;
    }
}