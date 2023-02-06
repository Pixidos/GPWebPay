<?php

declare(strict_types=1);

namespace GPWebPay\Tests;

use Nette\Application\AbortException;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;
use Pixidos\GPWebPay\Components\GPWebPayControl;
use Pixidos\GPWebPay\Components\GPWebPayControlFactory;
use Pixidos\GPWebPay\Data\Operation;
use Pixidos\GPWebPay\Enum\Currency as CurrencyEnum;
use Pixidos\GPWebPay\Exceptions\InvalidArgumentException;
use Pixidos\GPWebPay\Param\Amount;
use Pixidos\GPWebPay\Param\Currency;
use Pixidos\GPWebPay\Param\OrderNumber;
use Pixidos\GPWebPay\Param\ResponseUrl;
use UnexpectedValueException;

/**
 * Class TestPresenter
 * @package GPWebPay\Tests
 * @author  Ondra Votava <ondra@votava.dev>
 */
class TestPresenter extends Presenter
{
    public const ORDER_NUMBER = '123456';
    public const GATEWAY = 'czk';
    public const RESPONSE_URL = 'http://test.com';


    private $gpWebPayControlFactory;

    public function __construct(GPWebPayControlFactory $controlFactory)
    {
        parent::__construct();
        $this->gpWebPayControlFactory = $controlFactory;
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
     * @return GPWebPayControl
     */
    protected function createComponentPayControl(): GPWebPayControl
    {
        $control = $this->gpWebPayControlFactory->create(self::createOperation());

        return $control;
    }
    /**
     * @return Operation
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public static function createOperation(): Operation
    {
        return new Operation(
            new OrderNumber(self::ORDER_NUMBER),
            new Amount(1000),
            new Currency(CurrencyEnum::CZK()),
            self::GATEWAY,
            new ResponseUrl(self::RESPONSE_URL)
        );
    }
}
