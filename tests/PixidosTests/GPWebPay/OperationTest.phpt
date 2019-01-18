<?php
/**
 * Test: Pixidos\GPWebPay\OperationTest
 *
 * @testCase PixidosTest\GPWebPay\OperationTest
 */

namespace Pixidos\GPWebPayTest;

use Pixidos\GPWebPay\Exceptions\InvalidArgumentException;
use Pixidos\GPWebPay\Operation;
use PixidosTests\GPWebPay\GPWebPayTestCase;
use PixidosTests\GPWebPay\TestHelpers;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Class OperationTest
 * @package Pixidos\GPWebPayTest
 * @author Ondra Votava <ondra.votava@pixidos.com>
 *
 * @testCase
 */
class OperationTest extends GPWebPayTestCase
{
    
    
    public function testCreateOperation()
    {
        $operation = new Operation(123456, 1000.00, 203, 'CZK', 'http://response.com');
        
        Assert::same('123456', $operation->getOrderNumber());
        Assert::same(100000, $operation->getAmount());
        Assert::same(203, $operation->getCurrency());
        Assert::same('CZK', $operation->getGatewayKey());
        Assert::same('http://response.com', $operation->getResponseUrl());
    }
    
    public function testConversionForPenniesOff()
    {
        $operation = new Operation(123456, 100000, 203, 'CZK', 'http://response.com', false);
        
        Assert::same(100000, $operation->getAmount());
        
    }
    
    
    /**
     * @dataProvider getOrderNumberExceptionData
     *
     * @param $orderNum
     */
    public function testOrderNumberException($orderNum)
    {
        Assert::exception(
            function () use ($orderNum) {
                new Operation($orderNum, 1000, 203);
            }, InvalidArgumentException::class, 'ORDERNUMBER must be number "' . $orderNum . '" given'
        );
    }
    
    public function getOrderNumberExceptionData()
    {
        return [
            ['FA1234'],
            [9.2],
            ['9.2'],
        ];
    }
    
    public function testCreateOperationException()
    {
        
        $orderNum = '1234567890132456';
        Assert::exception(
            function () use ($orderNum) {
                new Operation($orderNum, 1000, 203);
            }, InvalidArgumentException::class, 'ORDERNUMBER max. length is 15! "' . strlen($orderNum) . '" given'
        );
        

        $amount = '1000';
        Assert::exception(
            function () use ($amount) {
                new Operation(123456, $amount, 203);
            }, InvalidArgumentException::class, 'AMOUNT must be type of INT or FLOAT ! "' . gettype($amount) . '" given'
        );
        
        
        $currency = 2030;
        Assert::exception(
            function () use ($currency) {
                new Operation(123456, 1000, $currency);
            }, InvalidArgumentException::class, 'CURRENCY code max. length is 3! "' . strlen($currency) . '" given'
        );
        
    }
    
    public function testResponseUrl()
    {
        $operation = TestHelpers::createOperation();
        Assert::same('http://test.com', $operation->getResponseUrl());
        $operation->setResponseUrl('http://example.com');
        Assert::same('http://example.com', $operation->getResponseUrl());
        
        $url = 'http://example.com/';
        for ($i = 0; $i < 31; $i++) {
            $url .= '1234567890/';
        }
        Assert::exception(
            function () use ($operation, $url) {
                $operation->setResponseUrl($url);
            }, InvalidArgumentException::class, 'URL max. length is 300! "' . strlen($url) . '" given'
        );
        
        $url = 'absdsdas';
        Assert::exception(
            function () use ($operation, $url) {
                $operation->setResponseUrl($url);
            }, InvalidArgumentException::class, 'URL is Invalid'
        );
        
    }
    
    /***/
    public function testMd()
    {
        $operation = TestHelpers::createOperation();
        
        $operation->setMd('some text');
        Assert::same('czk|some text', $operation->getMd());
        
        $md = 'some text and next string';
        for ($i = 0; $i < 26; $i++) {
            $md .= '1234567890/';
        }
        Assert::exception(
            function () use ($operation, $md) {
                $operation->setMd($md);
            }, InvalidArgumentException::class, 'MD max. length is 250! "' . strlen($md) . '" given'
        );
        
    }
    
    public function testDescription()
    {
        $operation = TestHelpers::createOperation();
        
        $operation->setDescription('some text');
        Assert::same('some text', $operation->getDescription());
        
        $description = 'some text and next string';
        for ($i = 0; $i < 26; $i++) {
            $description .= '1234567890/';
        }
        Assert::exception(
            function () use ($operation, $description) {
                $operation->setDescription($description);
            }, InvalidArgumentException::class, 'DESCRIPTION max. length is 255! "' . strlen($description) . '" given'
        );
        
    }
    
    public function testMerOrderNum()
    {
        $operation = TestHelpers::createOperation();
        
        $operation->setMerOrderNum('some information');
        Assert::same('some information', $operation->getMerOrderNum());
        
        $merordernum = 'some information';
        for ($i = 0; $i < 4; $i++) {
            $merordernum .= '1234567890/';
        }
        Assert::exception(
            function () use ($operation, $merordernum) {
                $operation->setMerOrderNum($merordernum);
            }, InvalidArgumentException::class, 'MERORDERNUM max. length is 30! "' . strlen($merordernum) . '" given'
        );
        
    }
    
    
    public function testLang()
    {
        $operation = TestHelpers::createOperation();
        
        $operation->setLang('cz');
        Assert::same('cz', $operation->getLang());
        
        $lang = 'usa';
        
        Assert::exception(
            function () use ($operation, $lang) {
                $operation->setLang($lang);
            }, InvalidArgumentException::class, 'LANG max. length is 2! "' . strlen($lang) . '" given'
        );
        
    }
    
    
    public function testUserParam1()
    {
        $operation = TestHelpers::createOperation();
        
        $operation->setUserParam1('some text');
        Assert::same('some text', $operation->getUserParam1());
        
        $userParam1 = 'some text and next string';
        for ($i = 0; $i < 26; $i++) {
            $userParam1 .= '1234567890/';
        }
        Assert::exception(
            function () use ($operation, $userParam1) {
                $operation->setUserParam1($userParam1);
            }, InvalidArgumentException::class, 'USERPARAM1 max. length is 255! "' . strlen($userParam1) . '" given'
        );
        
    }
    
    /**
     * Supported payMathods 'CRD','MCM','MPS','BTNCS', 'GPAY'
     */
    public function testPayMethod()
    {
        $operation = TestHelpers::createOperation();
        
        $operation->setPayMethod('CRD');
        Assert::same('CRD', $operation->getPayMethod());
        
        
        $payMethod = 'some text and next string';
        for ($i = 0; $i < 26; $i++) {
            $payMethod .= '1234567890/';
        }
        Assert::exception(
            function () use ($operation, $payMethod) {
                $operation->setPayMethod($payMethod);
            }, InvalidArgumentException::class, 'PAYMETHOD max. length is 255! "' . strlen($payMethod) . '" given'
        );
        
        $payMethod = 'CREDIT CARD';
        Assert::exception(
            function () use ($operation, $payMethod) {
                $operation->setPayMethod($payMethod);
            }, InvalidArgumentException::class, 'PAYMETHOD supported values: "CRD, MCM, MPS, BTNCS, GPAY" given: "' . strtoupper($payMethod) . '"'
        );
        
    }
    
    /**
     * Supported payMathods 'CRD','MCM','MPS','BTNCS','GPAY'
     */
    public function testDisablePayMethod()
    {
        $operation = TestHelpers::createOperation();
        
        $operation->setDisablePayMethod('CRD');
        Assert::same('CRD', $operation->getDisablePayMethod());
        
        
        $disablePayMethod = 'some text and next string';
        for ($i = 0; $i < 26; $i++) {
            $disablePayMethod .= '1234567890/';
        }
        Assert::exception(
            function () use ($operation, $disablePayMethod) {
                $operation->setDisablePayMethod($disablePayMethod);
            }, InvalidArgumentException::class, 'DISABLEPAYMETHOD max. length is 255! "' . strlen($disablePayMethod) . '" given'
        );
        
        $disablePayMethod = 'CREDIT CARD';
        Assert::exception(
            function () use ($operation, $disablePayMethod) {
                $operation->setDisablePayMethod($disablePayMethod);
            }, InvalidArgumentException::class, 'DISABLEPAYMETHOD supported values: "CRD, MCM, MPS, BTNCS, GPAY" given: "' . strtoupper($disablePayMethod) . '"'
        );
        
    }
    
    /**
     * Supported payMathods 'CRD','MCM','MPS','BTNCS','GPAY'
     */
    public function testPayMethods()
    {
        $operation = TestHelpers::createOperation();
        
        $operation->setPayMethods(['CRD', 'mcm']);
        Assert::same('CRD,MCM', $operation->getPayMethods());
        
        $payMethods = [];
        for ($i = 0; $i < 86; $i++) {
            $payMethods[] = 'CRD';
        }
        $str = implode(",", $payMethods);
        Assert::exception(
            function () use ($operation, $payMethods, $str) {
                $operation->setPayMethods($payMethods);
            }, InvalidArgumentException::class, 'PAYMETHODS max. length is 255! "' . strlen($str) . '" given'
        );
        
        $payMethods = ['ABC'];
        Assert::exception(
            function () use ($operation, $payMethods) {
                $operation->setPayMethods($payMethods);
            }, InvalidArgumentException::class, 'PAYMETHODS supported values: "CRD, MCM, MPS, BTNCS, GPAY" given: "ABC"'
        );
        
    }
    
    /***/
    public function testEmail()
    {
        $operation = TestHelpers::createOperation();
        $operation->setEmail('test@test.com');
        Assert::same('test@test.com', $operation->getEmail());
        
        $email = 'thisEmailNotWorking';
        Assert::exception(
            function () use ($operation, $email) {
                $operation->setEmail($email);
            }, InvalidArgumentException::class, 'EMAIL is not valid! "thisEmailNotWorking" given'
        );
        
        
        $email = 'thisEmailNotWorking';
        for ($i = 0; $i < 25; $i++) {
            $email .= '1234567890';
        }
        $email .= '@test.com';
        Assert::exception(
            function () use ($operation, $email) {
                $operation->setEmail($email);
            }, InvalidArgumentException::class, 'EMAIL max. length is 255! "' . strlen($email) . '" given'
        );
        
    }
    
    public function testReferenceNumber()
    {
        $operation = TestHelpers::createOperation();
        $operation->setReferenceNumber('123456789');
        Assert::same('123456789', $operation->getReferenceNumber());
        
        $referenceNumber = '1234567890123456789012';
        Assert::exception(
            function () use ($operation, $referenceNumber) {
                $operation->setReferenceNumber($referenceNumber);
            }, InvalidArgumentException::class, 'REFERENCENUMBER max. length is 20! "' . strlen($referenceNumber) . '" given'
        );
        
    }
    
    public function testFastPayId()
    {
        $operation = TestHelpers::createOperation();
        $operation->setFastPayId(15);
        Assert::same(15, $operation->getFastPayId());
        
        $fastPayId = '15A';
        Assert::exception(
            function () use ($operation, $fastPayId) {
                $operation->setFastPayId($fastPayId);
            }, InvalidArgumentException::class, 'FASTPAYID must be number "15A" given'
        );
        
        
        $fastPayId = 1234567890123456;
        Assert::exception(
            function () use ($operation, $fastPayId) {
                $operation->setFastPayId($fastPayId);
            }, InvalidArgumentException::class, 'FASTPAYID max. length is 15! "' . strlen($fastPayId) . '" given'
        );
        
    }
    
    
}

(new \Pixidos\GPWebPayTest\OperationTest())->run();
