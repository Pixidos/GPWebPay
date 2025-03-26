.. _usage:

==========
Usage
==========

Example:

.. code-block:: php

	use Nette;
	use Pixidos\GPWebPay\Components\GPWebPayControl;
	use Pixidos\GPWebPay\Components\GPWebPayControlFactory;
	use Pixidos\GPWebPay\Data\Operation;
	use Pixidos\GPWebPay\Data\RequestInterface;
	use Pixidos\GPWebPay\Data\ResponseInterface;
	use Pixidos\GPWebPay\Enum\Currency as CurrencyEnum;
	use Pixidos\GPWebPay\Exceptions\GPWebPayException;
	use Pixidos\GPWebPay\Factory\ResponseFactory;
	use Pixidos\GPWebPay\Param\AmountInPennies;
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
				new AmountInPennies(100000),
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

And in template

.. code-block:: smarty

	{var $attrs = array(class => 'btn btn-primary')}
	{control webPayButton $attrs, 'text on button'}

