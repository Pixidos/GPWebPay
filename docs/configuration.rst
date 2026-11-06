.. _configuration:

=============
Configuration
=============

You are need create configuration for gateway. Library supporting single or multiple gateways,
for example one per currency, each with its own merchant number and keys.

.. warning::
	Never commit private keys, passphrases or merchant credentials to version control, and keep key files
	outside the web root. Put them into a local configuration file that is not committed, or pass them to the
	container as dynamic parameters.


.. _configuration.attributes:

Attributes
##########

.. _configuration.attributes.privateKey:

The `privateKey` attribute
--------------------------

Absolute path to your private key ``pem`` file, generated in the GP webpay portal.

:Type: ``string``

.. _configuration.attributes.privateKeyPassphrase:

The `privateKeyPassphrase` attribute
------------------------------------

Passphrase of your private key.

:Type: ``string``

.. _configuration.attributes.publicKey:

The `publicKey` attribute
-------------------------

Absolute path to the GP webpay public key ``pem`` file, downloaded from the GP webpay portal.
It verifies the signatures of responses.

:Type: ``string``

.. _configuration.attributes.url:

The `url` attribute
-------------------

Absolute URL of the gateway. GP webpay HTTP API 1.19 lists:

* test environment: ``https://test.3dsecure.gpwebpay.com/pgw/order.do``
* production: ``https://3dsecure.gpwebpay.com/pgw/order.do``

:Type: ``string``

.. note::
	Use the address your provider gives you. Older integrations may use another path, such as
	``/unicredit/order.do``; this library does not change the configured URL.

.. _configuration.attributes.depositFlag:

The `depositFlag` attribute
---------------------------

| Specifies if the order has to be paid for automatically. Is optional with ``1`` as default.
| ``0`` = instant payment not required
| ``1`` = payment required

:Type: ``int``, ``0`` or ``1``



.. _configuration.attributes.merchantNumber:

The `merchantNumber` attribute
------------------------------

Your merchant number, assigned by the provider.

:Type: ``string``

.. _configuration.attributes.responseUrl:

The `responseUrl` attribute
---------------------------

Absolute URL of your page that receives the response, see :ref:`usage.response`.

:Type: ``string``

Optional. Each gateway can have its own URL; a request uses the URL of its gateway unless the operation sets
a ``ResponseUrl``. If neither is set, creating the request fails.

.. warning:: GP webpay may refuse addresses with query parameters (after ``?``) for security reasons.
	Test the real address in the test environment.

.. _configuration.validation:

Validation
##########

The configuration is checked when the DI container is compiled. A missing or unknown attribute, a ``depositFlag``
other than ``0`` or ``1`` and a value that is not a string are reported as ``Nette\DI\InvalidConfigurationException``
with the path of the attribute, for example ``gpwebpay › eur › merchantNumber``.

NEON reads unquoted digits as a number, so **write the merchant number and a numeric passphrase in quotes**:

.. code-block:: yaml

	merchantNumber: '123456789'     # a string, correct
	merchantNumber: 123456789       # a number, rejected

Numbers are not converted to strings, because NEON could already have changed them: ``0123`` or ``1e5`` are not
read as the digits you wrote. Values computed when the container runs, such as dynamic parameters or
``::getenv('NAME')``, are not checked when the container is compiled.

.. _configuration.gateways:

Gateway keys
############

With several gateways, the gateway key (``czk`` and ``eur`` in the example below) selects the merchant number, the
keys, the gateway URL and the response URL of a request. It is sent in the MD field, and the response is routed by it
to the same gateway. ``defaultGateway`` is used for operations that do not name a gateway; without it the default
gateway is ``default``.

Gateway keys are case-insensitive and stored in lowercase (``CZK`` and ``czk`` are the same gateway). They must consist
of printable ASCII characters without ``|`` and ``#``.

.. _configuration.example:

Examples
########

.. _configuration.example.single_gateway:

Single gateway
--------------

.. code-block:: yaml

	extensions:
		gpwebpay: Pixidos\GPWebPay\DI\GPWebPayExtension

	gpwebpay:
		privateKey: /path/outside/web/root/gpwebpay-private.pem
		privateKeyPassphrase: 'passphrase of the private key'
		publicKey: /path/outside/web/root/gpe.signing_test.pem
		url: https://test.3dsecure.gpwebpay.com/pgw/order.do
		merchantNumber: '123456789'
		responseUrl: https://shop.example/payment/gpwebpay-response # optional, the operation can set it
		depositFlag: 1 # optional, 0 or 1, default 1


.. _configuration.example.multiple_gateways:

Multiple gateways
-----------------

.. code-block:: yaml

	extensions:
		gpwebpay: Pixidos\GPWebPay\DI\GPWebPayExtension

	gpwebpay:
		czk:
			privateKey: /path/outside/web/root/gpwebpay-czk-private.pem
			privateKeyPassphrase: 'passphrase of the CZK key'
			publicKey: /path/outside/web/root/gpe.signing_test.pem
			url: https://test.3dsecure.gpwebpay.com/pgw/order.do
			merchantNumber: '123456789'
			responseUrl: https://shop.example/payment/gpwebpay-response # optional
			depositFlag: 1 # optional, 0 or 1, default 1
		eur:
			privateKey: /path/outside/web/root/gpwebpay-eur-private.pem
			privateKeyPassphrase: 'passphrase of the EUR key'
			publicKey: /path/outside/web/root/gpe.signing_test.pem
			url: https://test.3dsecure.gpwebpay.com/pgw/order.do
			merchantNumber: '123456780'
			responseUrl: https://shop.example/payment/gpwebpay-response-eur # optional, may differ per gateway
		defaultGateway: czk
