# Quickstart

This extension is here to provide [GP WebPay](http://www.gpwebpay.cz) system for Nette Framework.


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
    publicKey: < you public certificate path >
    url: <url of gpwabpay system gateway > //example: https://test.3dsecure.gpwebpay.com/unicredit/order.do
    merchantNumber: <your merechant number >   
```

or if you need more then one gateway
```yml
gpwebpay:
	privateKey:
		czk: < your CZK private certificate path .pem>
		eur: < your EUR private certificate path .pem>
	privateKeyPassword:
		czk: < private CKZ certificate password >
		eur: < private EUR certificate password >
	publicKey: < you public certificate path example > //gpe.signing_prod.pem
	url: <url of gpwabpay system gateway > //example: https://test.3dsecure.gpwebpay.com/unicredit/order.do
	merchantNumber:
		czk: <your CZK merechant number >
		eur: <your EUR merechant number >
```

## Usage


```php
use Pixidos\GPWebPay\Exceptions\GPWebPayException;
use Pixidos\GPWebPay\Request;
use Pixidos\GPWebPay\Response;
use Pixidos\GPWebPay\Operation;

class MyPresenter extends Nette\Application\UI\Presenter
{
	
	/** @var \Pixidos\GPWebPay\Components\GPWebPayControlFactory @inject */
	public $gpWebPayFactory;

	/**
     * @return GPWebPayControl
     * @throws InvalidArgumentException
     */
    public function createComponentWebPayButton()
    {
        $operation = new Operation(int $orderId, int $totalPrice, int $curencyCode);
        // if you use more than one gateway use gatewayKey - same as in config
        // $operation = new Operation(int $orderId, int $totalPrice, int $curencyCode, string $gatewayKey);
        
        /**
         * you can set Response URL. In default will be used handelSuccess() in component
         * https://github.com/Pixidos/GPWebPay/blob/master/src/Pixidos/GPWebPay/Components/GPWebPayControl.php#L93
         * $operation->setResponseUrl($url);
         */

        $control = $this->gpWebPayFactory->create($operation);
        
        # Run before redirect to webpay gateway
        $control->onCheckout[] = function (GPWebPayControl $control, Request $request){
        
            //...
           
        }
        

        # On success response 
        $control->onSuccess[] = function(GPWebPayControl $control, Response $response) {

            //....
            
        };

        # On Error
        $control->onError[] = function(GPWebPayControl $control, GPWebPayException $exception)
        {
            
            //...
            
        };

        return $control;

    }
}
```

## Templates

```smarty
{var $attrs = array(class => 'btn btn-primary')}
{control webPayButton $attrs, 'text on button'}
```
