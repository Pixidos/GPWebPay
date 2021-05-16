<?php

declare(strict_types=1);

namespace Pixidos\GPWebPay\Components;

use Closure;
use Nette\Application\AbortException;
use Nette\Application\UI;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\DefaultTemplate;
use Pixidos\GPWebPay\Data\OperationInterface;
use Pixidos\GPWebPay\Data\RequestInterface;
use Pixidos\GPWebPay\Exceptions\GPWebPayException;
use Pixidos\GPWebPay\Factory\RequestFactory;

/**
 * Class GPWebPayControl
 * @package Pixidos\GPWebPay\Components
 * @author  Ondra Votava <ondra@votava.dev>
 *
 * @method onCheckout(GPWebPayControl $control, RequestInterface $request)
 */
class GPWebPayControl extends UI\Control
{
    /**
     * @var Closure[], signature: function(GPWebPayControl $control)
     */
    public $onCheckout = [];

    /**
     * @var OperationInterface $operation
     */
    private $operation;
    /**
     * @var  string $templateFile
     */
    private $templateFile;
    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @param OperationInterface $operation
     * @param RequestFactory     $requestFactory
     *
     * @noinspection PhpMissingParentConstructorInspection
     * @noinspection MagicMethodsValidityInspection
     */
    public function __construct(OperationInterface $operation, RequestFactory $requestFactory)
    {
        $this->operation = $operation;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @throws AbortException
     * @throws GPWebPayException
     */
    public function handleCheckout(): void
    {
        $request = $this->requestFactory->create($this->operation);
        $url = $request->getRequestUrl();
        $this->onCheckout($this, $request);

        /** @var Presenter $presenter */
        $presenter = $this->getPresenter();
        $presenter->redirectUrl($url);
    }

    /**
     * @param string $templateFile
     */
    public function setTemplateFile(string $templateFile): void
    {
        $this->templateFile = $templateFile;
    }

    /**
     * @param mixed[] $attrs
     * @param string  $text
     *
     * @throws InvalidLinkException
     */
    public function render(array $attrs = [], string $text = 'Pay'): void
    {
        /** @var DefaultTemplate $template */
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
        return $this->templateFile ?? $this->getDefaultTemplateFilePath();
    }

    /**
     * @return string
     */
    public function getDefaultTemplateFilePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'templates/gpWebPayControl.latte';
    }
}
