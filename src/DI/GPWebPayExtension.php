<?php

declare(strict_types=1);

namespace Pixidos\GPWebPay\DI;

use Nette;
use Nette\Bootstrap\Configurator;
use Nette\DI\Compiler;
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

        $builder = $this->getContainerBuilder();

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
        /**
         * @phpstan-ignore-next-line
         */
        $configurator->onCompile[] = static function ($config, Compiler $compiler): void {
            $compiler->addExtension('gpwebpay', new GPWebPayExtension());
        };
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
