<?php

declare(strict_types=1);

namespace Pixidos\GPWebPay\DI;

use Nette;
use Nette\Bootstrap\Configurator;
use Nette\DI\Compiler;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\DI\InvalidConfigurationException;
use Nette\Schema\Context;
use Nette\Schema\DynamicParameter;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Elements\Type;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Nette\Schema\ValidationException;
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
 *
 * The configuration is either one gateway (its items directly in the section) or several named gateways,
 * optionally with defaultGateway. It is validated when the container is compiled:
 * credentials, URLs and the gateway name must be strings (quote numbers in NEON),
 * depositFlag must be 0 or 1, and unknown items are rejected.
 */
class GPWebPayExtension extends Nette\DI\CompilerExtension
{
    private const GATEWAY_KEY = 'defaultGateway';
    private const DEFAULT_GATEWAY = 'default';

    public function loadConfiguration(): void
    {
        $config = $this->processConfig((array)$this->getConfig());

        $defaultGateway = $config[self::GATEWAY_KEY];
        unset($config[self::GATEWAY_KEY]);

        $this->addService('paymentConfigFactory')
            ->setType(PaymentConfigFactory::class);

        $this->addService('configFactory')
            ->setType(ConfigFactory::class);
        $this->addService('config')
            ->setType(Config::class)
            ->setCreator(
                [$this->prefix('@configFactory'), 'create'],
                [$config, $defaultGateway]
            );

        $this->addService('paymentConfigProvider')
            ->setType(PaymentConfigProvider::class)
            ->setCreator(
                [$this->prefix('@config'), 'getPaymentConfigProvider']
            );

        $this->addService('signerConfigProvider')
            ->setType(SignerConfigProvider::class)
            ->setCreator(
                [$this->prefix('@config'), 'getSignerConfigProvider']
            );


        $this->addService('signerProvider')
            ->setType(SignerProviderInterface::class)
            ->setCreator(SignerProvider::class);

        $this->addService('signerFactory')
            ->setType(SignerFactoryInterface::class)
            ->setCreator(SignerFactory::class);

        $this->addService('requestFactory')
            ->setType(RequestFactory::class);

        $this->addService('responseFactory')
            ->setType(ResponseFactory::class);

        $this->addService('responseProvider')
            ->setType(ResponseProviderInterface::class)
            ->setCreator(
                ResponseProvider::class
            );

        $this->addService('controlFactory')
            ->setCreator(GPWebPayControlFactory::class, [$this->prefix('@requestFactory')]);
    }

    /**
     * @param Configurator $configurator
     */
    public static function register(Configurator $configurator): void
    {
        $configurator->onCompile[] = static function ($config, Compiler $compiler): void {
            $compiler->addExtension('gpwebpay', new GPWebPayExtension());
        };
    }

    /**
     * Registers an empty service definition under the prefix of the extension; the caller sets its type and creator.
     * nette/di 4 registers definitions with add(). nette/di 3.2, the only version for PHP 8.2, has only
     * addDefinition(), which nette/di 4 deprecates.
     */
    private function addService(string $name): ServiceDefinition
    {
        $builder = $this->getContainerBuilder();
        $definition = new ServiceDefinition();
        if (method_exists($builder, 'add')) {
            $builder->add($this->prefix($name), $definition);
        } else {
            $builder->addDefinition($this->prefix($name), $definition);
        }

        return $definition;
    }

    /**
     * Validates the configuration and fills in the defaults.
     * The shape is the one ConfigFactory expects: the items of one gateway, or gateway name => items.
     *
     * @param array<array-key, mixed> $config
     *
     * @return array<array-key, mixed>
     * @throws InvalidConfigurationException
     */
    private function processConfig(array $config): array
    {
        $processor = new Processor();
        $processor->onNewContext[] = function (Context $context): void {
            $context->path = [$this->name];
        };

        try {
            /** @var array<array-key, mixed> $result */
            $result = $processor->process($this->createConfigSchema($config), $config);
        } catch (ValidationException $exception) {
            throw new InvalidConfigurationException($exception->getMessage(), 0, $exception);
        }

        return $result;
    }

    /**
     * @param array<array-key, mixed> $config
     */
    private function createConfigSchema(array $config): Schema
    {
        $defaultGateway = self::createStringSchema()->default(self::DEFAULT_GATEWAY);

        if (!$this->isMultipleGateways($config)) {
            return self::createGatewaySchema([self::GATEWAY_KEY => $defaultGateway]);
        }

        $items = [self::GATEWAY_KEY => $defaultGateway];
        foreach (array_keys($config) as $gateway) {
            if (self::GATEWAY_KEY !== $gateway) {
                $items[$gateway] = self::createGatewaySchema();
            }
        }

        return Expect::structure($items)->castTo('array');
    }

    /**
     * Several gateways: every item except defaultGateway is an array (the configuration of one gateway).
     * Anything else, including an empty section, is validated as the configuration of one gateway.
     *
     * @param array<array-key, mixed> $config
     */
    private function isMultipleGateways(array $config): bool
    {
        if (array_key_exists(ConfigFactory::PRIVATE_KEY, $config)) {
            return false;
        }

        $gateways = array_diff_key($config, [self::GATEWAY_KEY => true]);

        return [] !== $gateways && count(array_filter($gateways, 'is_array')) === count($gateways);
    }

    /**
     * @param array<string, Schema> $extraItems
     */
    private static function createGatewaySchema(array $extraItems = []): Structure
    {
        return Expect::structure([
            ConfigFactory::PRIVATE_KEY => self::createStringSchema()->required(),
            ConfigFactory::PRIVATE_KEY_PASSPHRASE => self::createStringSchema()->required(),
            ConfigFactory::PUBLIC_KEY => self::createStringSchema()->required(),
            ConfigFactory::URL => self::createStringSchema()->required(),
            ConfigFactory::MERCHANT_NUMBER => self::createStringSchema()->required(),
            ConfigFactory::DEPOSIT_FLAG => Expect::mixed(1)->assert(
                static fn (mixed $value): bool => 0 === $value || 1 === $value || self::isDeferred($value),
                '0 or 1'
            ),
            ConfigFactory::RESPONSE_URL => Expect::mixed()->assert(
                static fn (mixed $value): bool => null === $value || is_string($value) || self::isDeferred($value),
                'string'
            ),
        ] + $extraItems)->castTo('array');
    }

    /**
     * A string. Numbers are rejected rather than converted: NEON reads unquoted values such as 0123 or 1e5
     * as numbers, and converting them back could silently change a merchant number or a passphrase.
     */
    private static function createStringSchema(): Type
    {
        return Expect::mixed()->assert(
            static fn (mixed $value): bool => is_string($value) || self::isDeferred($value),
            'string; write numbers in quotes'
        );
    }

    /**
     * A value computed when the container runs, such as ::getenv('NAME') or a dynamic parameter.
     */
    private static function isDeferred(mixed $value): bool
    {
        return $value instanceof Statement || $value instanceof DynamicParameter;
    }
}
