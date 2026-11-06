.. _usage:

==========
Usage
==========

The examples use your own storage of orders and payment attempts, shown as ``PaymentRepository``.
The full description of operations, parameters and responses is in the
`GPWebPay Core documentation <https://gpwebpay-core.readthedocs.io/en/latest/>`_.

.. _usage.request:

Payment button
##############

``GPWebPayControl`` renders a payment link. When the customer clicks it, the control creates and signs the request
from its ``Operation`` and redirects the customer to GP webpay.

.. code-block:: php

	use Nette;
	use Pixidos\GPWebPay\Components\GPWebPayControl;
	use Pixidos\GPWebPay\Components\GPWebPayControlFactory;
	use Pixidos\GPWebPay\Data\Operation;
	use Pixidos\GPWebPay\Data\RequestInterface;
	use Pixidos\GPWebPay\Enum\Currency as CurrencyEnum;
	use Pixidos\GPWebPay\Param\AmountInPennies;
	use Pixidos\GPWebPay\Param\Currency;
	use Pixidos\GPWebPay\Param\MerOrderNum;
	use Pixidos\GPWebPay\Param\OrderNumber;
	use Pixidos\GPWebPay\Param\ResponseUrl;
	use Tracy\Debugger;


	final class PaymentPresenter extends Nette\Application\UI\Presenter
	{
		private Order $order;

		public function __construct(
			private GPWebPayControlFactory $webPayControlFactory,
			private PaymentRepository $payments,
		) {
			parent::__construct();
		}

		public function actionPay(int $id): void
		{
			$this->order = $this->payments->findOrder($id) ?? $this->error();
			if ($this->order->isPaid()) {
				$this->redirect('Order:detail', $id);
			}
		}

		protected function createComponentWebPayButton(): GPWebPayControl
		{
			// Every request needs a new payment attempt: GP webpay requires an ORDERNUMBER that is unique for all
			// requests of the merchant, also for another attempt to pay the same order. Do not use time() or random
			// numbers, they can repeat. For example:
			// INSERT INTO payment_attempt (order_id, gateway, amount, currency, status) VALUES (?, 'czk', ?, 'CZK', 'pending')
			$attempt = $this->payments->createAttempt($this->order, 'czk');

			$operation = new Operation(
				orderNumber: new OrderNumber($attempt->id), // the AUTO_INCREMENT id of the attempt, at most 15 digits
				amount: new AmountInPennies($attempt->amount), // integer in hellers or cents: 129900 = 1 299.00 CZK
				currency: new Currency(CurrencyEnum::CZK()),
				gateway: 'czk', // optional, the default gateway is used when omitted
				responseUrl: new ResponseUrl($this->link('//Payment:response')), // optional when responseUrl is configured
			);
			// optional: your order number, numeric; GP webpay returns it in the response
			$operation->addParam(new MerOrderNum($this->order->id));

			$control = $this->webPayControlFactory->create($operation);

			// called after the request is signed, before the redirect to GP webpay
			$control->onCheckout[] = static function (GPWebPayControl $control, RequestInterface $request): void {
				Debugger::barDump($request); // Tracy
			};

			return $control;
		}
	}

.. note::
	Nette creates the component when the page with the button is rendered and again when the customer clicks it.
	Each time a new payment attempt is saved; the request sent to GP webpay belongs to the attempt created by the
	click. Attempts that never receive a response stay ``pending``.

And in the template; the button can be rendered more than once on a page:

.. code-block:: smarty

	{control webPayButton, ['class' => 'btn btn-primary'], 'Pay by card'}


.. _usage.response:

Response
########

GP webpay sends the result through the customer's browser to your response URL, either by a redirect (``GET``) or by
an automatically submitted form (``POST``). GP webpay chooses the method according to the services used, so the
response URL must accept both. Read the fields of the actual request method; never merge the query and the form
fields. Presenter parameters do not contain the form fields of a ``POST``.

The response action belongs to the same ``PaymentPresenter`` (``//Payment:response`` above); its constructor shows
the services it needs.

.. code-block:: php

	use Nette;
	use Pixidos\GPWebPay\Data\ResponseInterface;
	use Pixidos\GPWebPay\Exceptions\GPWebPayException;
	use Pixidos\GPWebPay\Exceptions\GPWebPayResultException;
	use Pixidos\GPWebPay\Exceptions\InvalidResponseException;
	use Pixidos\GPWebPay\Factory\ResponseFactory;
	use Pixidos\GPWebPay\ResponseProviderInterface;


	final class PaymentPresenter extends Nette\Application\UI\Presenter
	{
		public function __construct(
			private ResponseFactory $responseFactory,
			private ResponseProviderInterface $responseProvider,
			private PaymentRepository $payments,
		) {
			parent::__construct();
		}

		public function actionResponse(): void
		{
			$httpRequest = $this->getHttpRequest();
			$params = $httpRequest->isMethod('POST') ? $httpRequest->getPost() : $httpRequest->getQuery();

			try {
				$response = $this->responseFactory->create($params);
			} catch (InvalidResponseException) {
				// not a GP webpay response for this shop: change nothing
				$this->error('Invalid payment response.', 400);
			}

			$attempt = null;
			$this->responseProvider->addOnSuccess(
				function (ResponseInterface $response) use (&$attempt): void {
					// both signatures are valid, PRCODE and SRCODE are 0
					$attempt = $this->payments->markPaid($response->getOrderNumber(), $response->getGatewayKey());
				}
			);
			$this->responseProvider->addOnError(
				function (GPWebPayException $exception, ResponseInterface $response) use (&$attempt): void {
					if (!$exception instanceof GPWebPayResultException) {
						// SignerException: the signatures do not match; the data did not come from GP webpay
						// or were changed. Change nothing.
						$this->error('Invalid payment response.', 400);
					}
					// authentic response: the payment was declined, cancelled or failed
					$attempt = $this->payments->markFailed($response->getOrderNumber(), $response->getGatewayKey());
				}
			);

			// verifies DIGEST and DIGEST1 first, then calls the success callbacks or the error callbacks
			$this->responseProvider->provide($response);

			if (null === $attempt) {
				$this->error('Unknown payment attempt.');
			}
			$this->redirect('Order:detail', $attempt->orderId);
		}
	}

The error callbacks receive ``GPWebPayException``, the common parent of ``SignerException`` (the response is not
authentic) and ``GPWebPayResultException`` (an authentic response of a payment that did not succeed). A callback
declared with a narrower type fails with a ``TypeError``. ``ResponseProviderInterface`` is one shared service, so
register the callbacks and call ``provide()`` once per request.

.. _usage.response.processing:

Processing the payment attempt
------------------------------

The library does not store payments. ``PaymentRepository`` in the example has to:

* find the payment attempt by ORDERNUMBER (``$response->getOrderNumber()``) and check that it belongs to the gateway
  ``$response->getGatewayKey()``; return ``null`` for unknown attempts and change nothing. DIGEST1 contains the
  merchant number of that gateway, so ``provide()`` rejects a response signed for another merchant;
* take the amount and currency from the attempt. The response does not repeat them; they were signed in the request
  with this ORDERNUMBER;
* change the state atomically and only once, in one transaction with the order, for example
  ``UPDATE payment_attempt SET status = 'paid' WHERE id = ? AND status = 'pending'``, and fulfil the order only when
  this statement changed a row. The same signed response can arrive again (page refresh, back button, replay) and must
  not fulfil the order twice. ``markFailed()`` must likewise change only a ``pending`` attempt;
* not depend on the customer's session: a ``POST`` from GP webpay is a cross-site request, so browsers do not send
  cookies with ``SameSite=Lax``, including the Nette session cookie;
* not rely on the browser returning at all. If no response arrives, ask GP webpay for the payment state through its
  Web Services.

.. _usage.response.data:

Response data
-------------

* ``getMerOrderNumber()`` returns MERORDERNUM, if the request contained it.
* ``getMd()`` returns the merchant data added to the operation with the ``Md`` parameter, without the gateway prefix
  that the library adds and without the ``#ID=…`` or ``#IDPP=…`` suffix that GP webpay may append. The signatures
  are verified over the complete received value.
* Since HTTP API 1.17 GP webpay returns the card token in TOKEN instead of USERPARAM1. Read it with
  ``$response->getParams()``, see the GPWebPay Core documentation.
