<?php declare(strict_types=1);

namespace Pixidos\GPWebPay\Components;

use Closure;
use Nette\Application\AbortException;
use Nette\Application\UI;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use Pixidos\GPWebPay\Data\IOperation;
use Pixidos\GPWebPay\Data\IRequest;
use Pixidos\GPWebPay\Data\IResponse;
use Pixidos\GPWebPay\Enum\Param;
use Pixidos\GPWebPay\Exceptions\GPWebPayException;
use Pixidos\GPWebPay\IProvider;
use Pixidos\GPWebPay\Param\ResponseUrl;

/**
 * Class GPWebPayControl
 * @package Pixidos\GPWebPay\Components
 * @author  Ondra Votava <ondra@votava.dev>
 *
 * @method onCheckout(GPWebPayControl $control, IRequest $request)
 * @method onSuccess(GPWebPayControl $control, IResponse $response)
 * @method onError(GPWebPayControl $control, GPWebPayException $exception)
 */
class GPWebPayControl extends UI\Control
{
    /**
     * @var Closure[], signature: function(GPWebPayControl $control)
     */
    public $onCheckout = [];
    /**
     * @var Closure[], signature: function(GPWebPayControl $control, Response $response)
     */
    public $onSuccess = [];
    /**
     * @var Closure[], signature: function(GPWebPayControl $control, GPWebPayException $exception)
     */
    public $onError = [];
    /**
     * @var IOperation $operation
     */
    private $operation;
    /**
     * @var  IProvider $provider
     */
    private $provider;
    /**
     * @var  string $templateFile
     */
    private $templateFile;

    /**
     * @param IOperation $operation
     * @param IProvider  $provider
     */
    public function __construct(IOperation $operation, IProvider $provider)
    {
        //parent::__construct($control, $name);

        $this->operation = $operation;
        $this->provider = $provider;
    }

    /**
     * @throws AbortException
     * @throws InvalidLinkException
     * @throws GPWebPayException
     */
    public function handleCheckout(): void
    {
        try {
            if ($this->operation->getParam(Param::RESPONSE_URL()) === null) {
                $this->operation->addParam(new ResponseUrl($this->link('//success!')));
            }

            $request = $this->provider->createRequest($this->operation);
            $url = $request->getRequestUrl();
            $this->onCheckout($this, $request);

            /** @var Presenter $presenter */
            $presenter = $this->getPresenter();
            $presenter->redirectUrl($url);
        } catch (GPWebPayException $e) {
            $this->errorHandler($e);

            return;
        }
    }


    /**
     * @throws GPWebPayException
     */
    public function handleSuccess(): void
    {
        /** @var Presenter $presenter */
        $presenter = $this->getPresenter();
        $params = $presenter->getParameters();

        try {
            /** @var IResponse $response */
            $response = $this->provider->createResponse($params);
            $this->provider->verifyPaymentResponse($response);
        } catch (GPWebPayException $e) {
            $this->errorHandler($e);

            return;
        }

        $this->onSuccess($this, $response);
    }

    /**
     * @param string $templateFile
     */
    public function setTemplateFile(string $templateFile): void
    {
        $this->templateFile = $templateFile;
    }

    /**
     * @param array  $attrs
     * @param string $text
     *
     * @throws InvalidLinkException
     */
    public function render(array $attrs = [], string $text = 'Pay'): void
    {
        /** @var Template $template */
        $template = $this->getTemplate();
        $template->setFile($this->getTemplateFilePath());
        $template->add('checkoutLink', $this->link('//checkout!'));
        $template->add('text', $text);
        $template->add('attrs', $attrs);
        $template->render();
    }

    /**
     * @return string
     */
    public function getTemplateFilePath(): string
    {
        return $this->templateFile ?: $this->getDefaultTemplateFilePath();
    }

    /**
     * @return string
     */
    public function getDefaultTemplateFilePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'templates/gpWebPayControl.latte';
    }

    /**
     * @param GPWebPayException $exception
     *
     * @throws GPWebPayException
     */
    protected function errorHandler(GPWebPayException $exception): void
    {
        if (count($this->onError) === 0) {
            throw $exception;
        }

        $this->onError($this, $exception);
    }
}
