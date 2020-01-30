# Quickstart

This extension is here to provide [GP WebPay](http://www.gpwebpay.cz) service for Nette Framework.


## Installation

The best way to install Pixidos/GPWebPay is using  [Composer](http://getcomposer.org/):

```sh
$ composer require pixidos/gpwebpay
```

and you can enable the extension using your neon config

```yml
extensions:
    gpwebpay: Pixidos\GPWebPay\DI\GPWebPayExtension
```

and setting

```yml
gpwebpay:
    privateKey: < your private certificate path >
    privateKeyPassword: < private certificate password >
    publicKey: < gateway public certificate path (you will probably get this by email) > //gpe.signing_prod.pem
    url: <url of gpwabpay system gateway > //example: https://test.3dsecure.gpwebpay.com/unicredit/order.do
    merchantNumber: <your merechant number >
    responseUrl: <on this url client get redirect back after payment will done> #optional you can set in Control
    depositFlag: 1 #optional you can set in Operation. Can set 1 or 0. Default is 1
```

or if you need more then one gateway
```yml
gpwebpay:
    czk:
        privateKey: < your private certificate path >
        privateKeyPassword: < private certificate password >
        publicKey: < gateway public certificate path (you will probably get this by email) > //gpe.signing_prod.pem
        url: <url of gpwabpay system gateway > //example: https://test.3dsecure.gpwebpay.com/unicredit/order.do
        merchantNumber: <your merechant number >
        responseUrl: <on this url client get redirect back after payment will done> #optional you can set in Control
        depositFlag: 1 #optional you can set in Operation. Can set 1 or 0. Default is 1
    eur:
        privateKey: < your private certificate path >
        privateKeyPassword: < private certificate password >
        publicKey: < gateway public certificate path (you will probably get this by email) > //gpe.signing_prod.pem
        url: <url of gpwabpay system gateway > //example: https://test.3dsecure.gpwebpay.com/unicredit/order.do
        merchantNumber: <your merechant number >
    defaultGateway: czk #eur
```

## Usage


```php
<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Pixidos\GPWebPay\Components\GPWebPayControl;
use Pixidos\GPWebPay\Components\GPWebPayControlFactory;
use Pixidos\GPWebPay\Data\Operation;
use Pixidos\GPWebPay\Data\RequestInterface;
use Pixidos\GPWebPay\Data\ResponseInterface;
use Pixidos\GPWebPay\Enum\Currency as CurrencyEnum;
use Pixidos\GPWebPay\Exceptions\GPWebPayException;
use Pixidos\GPWebPay\Factory\ResponseFactory;
use Pixidos\GPWebPay\Param\Amount;
use Pixidos\GPWebPay\Param\Currency;
use Pixidos\GPWebPay\Param\OrderNumber;
use Pixidos\GPWebPay\Param\ResponseUrl;
use Pixidos\GPWebPay\ResponseProviderInterface;



final class PaymentPresenter extends Nette\Application\UI\Presenter
{

    /**
     * @var GPWebPayControlFactory
     */
    private $webPayControlFactory;
    /**
     * @var ResponseProviderInterface
     */
    private $responseProvider;
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    public function __construct(GPWebPayControlFactory $webPayControlFactory, ResponseProviderInterface $responseProvider, ResponseFactory $responseFactory)
    {
        parent::__construct();
        $this->webPayControlFactory = $webPayControlFactory;
        $this->responseProvider = $responseProvider;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @return GPWebPayControl
     */
    protected function createComponentWebPayButton(): GPWebPayControl
    {
        $operation = new Operation(
            new OrderNumber(time()),
            new Amount(1000),
            new Currency(CurrencyEnum::CZK()),
            'czk', // leave empty or null for default key
        new ResponseUrl($this->link('//Payment:processResponse')) // you can setup by config responseUrl:
        );

        $control = $this->webPayControlFactory->create($operation);
        ;

        # Run before redirect to webpay gateway
        $control->onCheckout[] = static function (GPWebPayControl $control, RequestInterface $request) {
            Debugger::barDump($request);
        };


        return $control;
    }

    public function actionProcessResponse()
    {
        $response = $this->responseFactory->create($this->getParameters());
        $this->responseProvider->addOnSuccess(
            static function (ResponseInterface $response) {
                //.. process success response
            }
        );

        $this->responseProvider->addOnError(
            static function (GPWebPayException $exception, ResponseInterface $response) {
                //.. process error response
            }
        );

        $this->responseProvider->provide($response);
    }
}

```

## Templates

```smarty
{var $attrs = array(class => 'btn btn-primary')}
{control webPayButton $attrs, 'text on button'}
```
