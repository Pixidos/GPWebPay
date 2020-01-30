<?php declare(strict_types=1);

namespace Pixidos\GPWebPay\DI;

use Nette;
use Nette\Configurator;
use Nette\DI\Compiler;
use Pixidos\GPWebPay\Components\GPWebPayControlFactory;
use Pixidos\GPWebPay\Config\Config;
use Pixidos\GPWebPay\Config\Factory\ConfigFactory;
use Pixidos\GPWebPay\Config\Factory\PaymentConfigFactory;
use Pixidos\GPWebPay\Config\PaymentConfigProvider;
use Pixidos\GPWebPay\Config\SignerConfigProvider;
use Pixidos\GPWebPay\Factory\RequestFactory;
use Pixidos\GPWebPay\Factory\ResponseFactory;
use Pixidos\GPWebPay\ResponseProvider;
use Pixidos\GPWebPay\ResponseProviderInterface;
use Pixidos\GPWebPay\Signer\SignerFactory;
use Pixidos\GPWebPay\Signer\SignerFactoryInterface;
use Pixidos\GPWebPay\Signer\SignerProvider;
use Pixidos\GPWebPay\Signer\SignerProviderInterface;

/**
 * Class GPWebPayExtension
 * @package Pixidos\GPWebPay\DI
 * @author  Ondra Votava <ondra@votava.dev>
 */
class GPWebPayExtension extends Nette\DI\CompilerExtension
{

    private const GATEWAY_KEY = 'defaultGateway';

    public function loadConfiguration(): void
    {
        $config = $this->getConfig();
        if (is_object($config)) {
            $config = (array)$config;
        }

        $builder = $this->getContainerBuilder();

        $defaultGateway = 'czk';
        if (array_key_exists(self::GATEWAY_KEY, $config)) {
            $defaultGateway = $config[self::GATEWAY_KEY];
            unset($config[self::GATEWAY_KEY]);
        }

        $builder->addDefinition($this->prefix('paymentConfigFactory'))
            ->setType(PaymentConfigFactory::class);

        $builder->addDefinition($this->prefix('configFactory'))
            ->setType(ConfigFactory::class);
        $builder->addDefinition($this->prefix('config'))
            ->setType(Config::class)
            ->setFactory(
                [$this->prefix('@configFactory'), 'create'],
                [$config, $defaultGateway]
            );

        $builder->addDefinition($this->prefix('paymentConfigProvider'))
            ->setType(PaymentConfigProvider::class)
            ->setFactory(
                [$this->prefix('@config'), 'getPaymentConfigProvider']
            );

        $builder->addDefinition($this->prefix('signerConfigProvider'))
            ->setType(SignerConfigProvider::class)
            ->setFactory(
                [$this->prefix('@config'), 'getSignerConfigProvider']
            );


        $builder->addDefinition($this->prefix('signerProvider'))
            ->setType(SignerProviderInterface::class)
            ->setFactory(SignerProvider::class);

        $builder->addDefinition($this->prefix('signerFactory'))
            ->setType(SignerFactoryInterface::class)
            ->setFactory(SignerFactory::class);

        $builder->addDefinition($this->prefix('requestFactory'))
            ->setType(RequestFactory::class);

        $builder->addDefinition($this->prefix('responseFactory'))
            ->setType(ResponseFactory::class);

        $builder->addDefinition($this->prefix('responseProvider'))
            ->setType(ResponseProviderInterface::class)
            ->setFactory(
                ResponseProvider::class
            );

        $builder->addDefinition($this->prefix('controlFactory'))
            ->setFactory(GPWebPayControlFactory::class, [$this->prefix('@requestFactory')]);
    }

    /**
     * @param Configurator $configurator
     */
    public static function register(Configurator $configurator): void
    {
        $configurator->onCompile[] = static function ($config, Compiler $compiler) {
            $compiler->addExtension('gpwebpay', new GPWebPayExtension());
        };
    }
}
