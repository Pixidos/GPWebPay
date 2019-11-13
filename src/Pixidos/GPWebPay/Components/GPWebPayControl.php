<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 15:09
 */

namespace Pixidos\GPWebPay\Components;

use Nette\Application\UI;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\ComponentModel\IContainer;
use Pixidos\GPWebPay\Exceptions\GPWebPayException;
use Pixidos\GPWebPay\Intefaces\IOperation;
use Pixidos\GPWebPay\Intefaces\IProvider;
use Pixidos\GPWebPay\Intefaces\IRequest;
use Pixidos\GPWebPay\Intefaces\IResponse;


/**
 * Class GPWebPayControl
 * @package Pixidos\GPWebPay\Components
 * @author Ondra Votava <ondra.votava@pixidos.com>
 *
 * @method onCheckout(GPWebPayControl $control, IRequest $request)
 * @method onSuccess(GPWebPayControl $control, IResponse $response)
 * @method onError(GPWebPayControl $control, GPWebPayException $exception)
 */
class GPWebPayControl extends UI\Control
{
    /**
     * @var \Closure[], signature: function(GPWebPayControl $control)
     */
    public $onCheckout = [];
    /**
     * @var \Closure[], signature: function(GPWebPayControl $control, Response $response)
     */
    public $onSuccess = [];
    /**
     * @var \Closure[], signature: function(GPWebPayControl $control, GPWebPayException $exception)
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
     * @param IOperation      $operation
     * @param IProvider       $provider
     * @param IContainer|null $control
     * @param string          $name
     */
    public function __construct(IOperation $operation, IProvider $provider, IContainer $control = null, $name = null)
    {
        $this->operation = $operation;
        $this->provider = $provider;
    }
    
    /**
     * @throws \Nette\Application\AbortException
     * @throws UI\InvalidLinkException
     * @throws GPWebPayException
     */
    public function handleCheckout()
    {
        try {
            if (!$this->operation->getResponseUrl()) {
                $this->operation->setResponseUrl($this->link('//success!'));
            }
            
            $url = $this->provider->createRequest($this->operation)->getRequestUrl();
            $this->onCheckout($this, $this->provider->getRequest());
            
            if (null === $presenter = $this->getPresenter()) {
                throw new GPWebPayException('Component need attach presenter');
            }
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
        if (null === $presenter = $this->getPresenter()) {
            throw new GPWebPayException('Component need attach presenter');
        }
        
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
    public function setTemplateFile(string $templateFile)
    {
        $this->templateFile = $templateFile;
    }
    
    /**
     * @param array  $attrs
     * @param string $text
     *
     * @throws UI\InvalidLinkException
     */
    public function render(array $attrs = [], string $text = 'Pay')
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
        if (!$this->onError) {
            throw $exception;
        }
        
        $this->onError($this, $exception);
    }
}
