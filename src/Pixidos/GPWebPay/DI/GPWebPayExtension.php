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
use Pixidos\GPWebPay\Settings;
use Pixidos\GPWebPay\Components\GPWebPayControlFactory;
use Pixidos\GPWebPay\Provider;

/**
 * Class GPWebPayExtension
 * @package Pixidos\GPWebPay\DI
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */

class GPWebPayExtension extends Nette\DI\CompilerExtension
{
    public $defaults = [
        'depositFlag' => 1,
        'gatewayKey' => 'czk'
    ];

    public function loadConfiguration()
    {
        $config = $this->getConfig();

        $defaults = array_diff_key($this->defaults, $config);
        foreach ($defaults as $key => $val){
            $config[$key] = $this->defaults[$key];
        }

        Validators::assertField($config, 'privateKey');
        Validators::assertField($config, 'privateKeyPassword');
        Validators::assertField($config, 'publicKey');
        Validators::assertField($config, 'url');
        Validators::assertField($config, 'merchantNumber');
        Validators::assertField($config, 'depositFlag');
        Validators::assertField($config, 'gatewayKey');

        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('settings'))
            ->setClass(
                Settings::class, [
                'privateKey' => $config['privateKey'],
                'privateKeyPassword' => $config['privateKeyPassword'],
                'publicKey' => $config['publicKey'],
                'url' => $config['url'],
                'merchantNumber' => $config['merchantNumber'],
                'depositFlag' => $config['depositFlag'],
                'gatewayKey' => $config['gatewayKey']
            ]
            );

        $builder->addDefinition($this->prefix('provider'))
            ->setClass(Provider::class, [$this->prefix('@settings')]);

        $builder->addDefinition($this->prefix('controlFactory'))
            ->setClass(GPWebPayControlFactory::class, array($this->prefix('@provider')));

    }

    public static function register(Nette\Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
            $compiler->addExtension('gpwebpay', new GPWebPayExtension());
        };
    }
}
