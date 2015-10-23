<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 13:10
 */

namespace Pixidos\GPWebPay\DI;
use Nette;
use Nette\Utils\Validators;

/**
 * Class GPWebPayExtension
 * @package Pixidos\GPWebPay\DI
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */

class GPWebPayExtension extends Nette\DI\CompilerExtension
{
    public $defaults = ['depositFlag' => 1];

    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);

        Validators::assertField($config, 'privateKey');
        Validators::assertField($config, 'privateKeyPassword');
        Validators::assertField($config, 'publicKey');
        Validators::assertField($config, 'url');
        Validators::assertField($config, 'merchantNumber');
        Validators::assertField($config, 'depositFlag');

        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('settings'))
            ->setClass('Pixidos\GPWebPay\GPWebPaySettings', array(
                "privateKey" => $config["privateKey"],
                "privateKeyPassword" => $config['privateKeyPassword'],
                'publicKey' => $config['publicKey'],
                'url' => $config['url'],
                'merchantNumber' => $config['merchantNumber'],
                'depositFlag' => $config['depositFlag']
            ));

        $builder->addDefinition($this->prefix('provider'))
            ->setClass('Pixidos\GPWebPay\GPWebPayProvider', array($this->prefix('@settings')));

        $builder->addDefinition($this->prefix('controlFactory'))
            ->setClass('Pixidos\GPWebPay\Components\GPWebPayControlFactory', array($this->prefix('@provider')));

    }

    public static function register(Nette\Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
            $compiler->addExtension('gpwebpay', new GPWebPayExtension());
        };
    }
}