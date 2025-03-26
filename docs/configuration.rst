.. _configuration:

=============
Configuration
=============

You are need create configuration for gateway. Library supporting single or multiple gateways,
because for every currency are need one.



.. _configuration.attributes:

Attributes
##########

.. _configuration.attributes.privateKey:

The `privateKey` attribute
--------------------------

Absolute path to your private certificate `pem` file.

:Type: ``string``

.. _configuration.attributes.privateKeyPassphrase:

The `privateKeyPassphrase` attribute
------------------------------------

Passphrase of your private certificate

:Type: ``string``

.. _configuration.attributes.publicKey:

The `publicKey` attribute
-------------------------

Absolute path to GPWebPay service public certificate ``pem`` file.

:Type: ``string``

.. _configuration.attributes.url:

The `url` attribute
-------------------

Absolute URL to gateway

:Type: ``string``

.. note::
	You are get from GPWebPay service

.. _configuration.attributes.depositFlag:

The `depositFlag` attribute
---------------------------

| Specifies if the order has to be paid for automatically. Is optional with ``1`` as default.
| ``0`` = instant payment not required
| ``1`` = payment required

:Type: ``int``



.. _configuration.attributes.merchantNumber:

The `merchantNumber` attribute
------------------------------

Your Merchant Number

:Type: ``string``

.. note::
	You are get from GPWebPay service.

.. _configuration.attributes.responseUrl:

The `responseUrl` attribute
---------------------------

Absolute URL to your site where GPWebPay sends a response

:Type: ``string``

Is optional in config. You can set up url for each request in Operation object.

.. warning:: GPWebPay is recommendation does not use url which has parameters (after '?')
	because they may drop it for *"security reason"*.

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
		privateKey: < your private certificate path >
		privateKeyPassphrase: < private certificate passphrase >
		publicKey: < gateway public certificate path> # (you will probably get this by email) gpe.signing_prod.pem
		url: <url of gpwabpay system gateway > #example: https://test.3dsecure.gpwebpay.com/unicredit/order.do
		merchantNumber: <your merechant number >
		responseUrl: <on this url client get redirect back after payment will done> #optional you can set in Control
		depositFlag: 1 #Can set 1 or 0. Default is 1 and you can rewrite in Operation


.. _configuration.example.multiple_gateways:

Multiple gateways
-----------------

.. code-block:: yaml

	extensions:
		gpwebpay: Pixidos\GPWebPay\DI\GPWebPayExtension

	gpwebpay:
		czk:
			privateKey: < your private certificate path >
			privateKeyPassphrase: < private certificate password >
			publicKey: < gateway public certificate path (you will probably get this by email) > //gpe.signing_prod.pem
			url: <url of gpwabpay system gateway > #example: https://test.3dsecure.gpwebpay.com/unicredit/order.do
			merchantNumber: <your merechant number >
			responseUrl: <on this url client get redirect back after payment will done> #optional you can set in Control
			depositFlag: 1 #optional you can set in Operation. Can set 1 or 0. Default is 1
		eur:
			privateKey: < your private certificate path >
			privateKeyPassphrase: < private certificate password >
			publicKey: < gateway public certificate path (you will probably get this by email) > //gpe.signing_prod.pem
			url: <url of gpwabpay system gateway > #example: https://test.3dsecure.gpwebpay.com/unicredit/order.do
			merchantNumber: <your merechant number >
		defaultGateway: czk #eur
