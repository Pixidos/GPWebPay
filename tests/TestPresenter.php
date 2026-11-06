<?php

declare(strict_types=1);

namespace GPWebPay\Tests;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;
use Pixidos\GPWebPay\Components\GPWebPayControl;
use Pixidos\GPWebPay\Components\GPWebPayControlFactory;
use Pixidos\GPWebPay\Data\Operation;
use Pixidos\GPWebPay\Data\ResponseInterface;
use Pixidos\GPWebPay\Enum\Currency as CurrencyEnum;
use Pixidos\GPWebPay\Exceptions\GPWebPayException;
use Pixidos\GPWebPay\Exceptions\GPWebPayResultException;
use Pixidos\GPWebPay\Exceptions\InvalidArgumentException;
use Pixidos\GPWebPay\Exceptions\InvalidResponseException;
use Pixidos\GPWebPay\Factory\ResponseFactory;
use Pixidos\GPWebPay\Param\AmountInPennies;
use Pixidos\GPWebPay\Param\Currency;
use Pixidos\GPWebPay\Param\OrderNumber;
use Pixidos\GPWebPay\Param\ResponseUrl;
use Pixidos\GPWebPay\ResponseProviderInterface;
use UnexpectedValueException;

/**
 * Class TestPresenter
 * @package GPWebPay\Tests
 * @author  Ondra Votava <ondra@votava.dev>
 */
class TestPresenter extends Presenter
{
    public const ORDER_NUMBER = '123456';

    public const RESPONSE_URL = 'http://test.com';


    private GPWebPayControlFactory $gpWebPayControlFactory;
    private ResponseFactory $responseFactory;
    private ResponseProviderInterface $responseProvider;

    public function __construct(
        GPWebPayControlFactory $controlFactory,
        ResponseFactory $responseFactory,
        ResponseProviderInterface $responseProvider
    ) {
        // nette/application 3.1 and 3.2 initialize the payload in the constructor, 4.0 has no constructor
        if (method_exists(Presenter::class, '__construct')) {
            parent::__construct();
        }
        $this->gpWebPayControlFactory = $controlFactory;
        $this->responseFactory = $responseFactory;
        $this->responseProvider = $responseProvider;
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function renderFake(): void
    {
        $response = new TextResponse('text');
        $this->sendResponse($response);
    }

    /**
     * The response handler of docs/usage.rst, with the payment attempt storage replaced by the text response.
     *
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionProcessResponse(): void
    {
        $httpRequest = $this->getHttpRequest();
        $params = $httpRequest->isMethod('POST') ? $httpRequest->getPost() : $httpRequest->getQuery();

        try {
            $response = $this->responseFactory->create($params);
        } catch (InvalidResponseException) {
            $this->error('Invalid payment response.', 400);
        }

        $result = null;
        $this->responseProvider->addOnSuccess(
            static function (ResponseInterface $response) use (&$result): void {
                $result = sprintf('paid %s %s', $response->getOrderNumber(), $response->getGatewayKey());
            }
        );
        $this->responseProvider->addOnError(
            function (GPWebPayException $exception, ResponseInterface $response) use (&$result): void {
                if (!$exception instanceof GPWebPayResultException) {
                    $this->error('Invalid payment response.', 400);
                }
                $result = sprintf('failed %s %s', $response->getOrderNumber(), $response->getGatewayKey());
            }
        );
        $this->responseProvider->provide($response);

        $this->sendResponse(new TextResponse((string)$result));
    }

    protected function createComponentPayControl(): GPWebPayControl
    {
        $control = $this->gpWebPayControlFactory->create(self::createOperation());

        return $control;
    }

    /**
     * Operation for the "eur" gateway without its own response URL: the configured URL of that gateway is used.
     */
    protected function createComponentEurPayControl(): GPWebPayControl
    {
        return $this->gpWebPayControlFactory->create(
            new Operation(
                orderNumber: new OrderNumber(self::ORDER_NUMBER),
                amount: new AmountInPennies(100000),
                currency: new Currency(CurrencyEnum::EUR()),
                gateway: 'eur'
            )
        );
    }

    /**
     * @return Operation
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public static function createOperation(): Operation
    {
        return new Operation(
            orderNumber: new OrderNumber(self::ORDER_NUMBER),
            amount: new AmountInPennies(100000),
            currency: new Currency(CurrencyEnum::CZK()),
            responseUrl: new ResponseUrl(self::RESPONSE_URL)
        );
    }
}
