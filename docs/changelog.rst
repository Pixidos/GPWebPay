.. _changelog:

=========
Changelog
=========

Unreleased
##########

Fixes:

    - ``GPWebPayControl`` can be rendered more than once, for example to show the payment button in two places of a page.
      The second ``render()`` threw "The variable 'checkoutLink' already exists".
    - The usage example reads a GP webpay response from the form fields of a ``POST`` and from the query of a ``GET``;
      presenter parameters do not contain the form fields, so ``POST`` responses failed. It handles errors with
      ``GPWebPayException``, stops on invalid responses, processes a payment attempt only once and uses a stored unique
      payment attempt instead of ``time()`` as ORDERNUMBER.
    - Requires pixidos/gpwebpay-core ^2.4.3: a request of a non-default gateway uses the response URL of that gateway,
      responses with ACRC, RRN, PAR, TRACEID or an MD suffix pass, and gateway keys are case-insensitive.

Changes:

    - The configuration is validated when the container is compiled. A missing or unknown item, a ``depositFlag`` other
      than 0 or 1 and a value that is not a string, such as an unquoted numeric ``merchantNumber`` or
      ``privateKeyPassphrase``, throw ``Nette\DI\InvalidConfigurationException`` with the path of the item instead of
      a ``TypeError`` when the service is first used, or being ignored.
    - PHP ^8.1, as required by pixidos/gpwebpay-core 2.4.
    - Documentation: requirements, GP webpay HTTP API 1.19 gateway addresses, configuration validation and gateway keys.
    - Development: tests run with nette/application 3.3, CI tests PHP 8.2 to 8.5 and runs ``composer audit``,
      the unused abandoned nette/finder was removed.

Version 3.4.1
#############

    - requires Pixidos/gpwebpay-core ~2.4
    - ``depositFlag`` is optional with one or multiple gateways, the default is 1
    - the default gateway without ``defaultGateway`` is ``default``
    - nette/bootstrap moved to require-dev

Verison 3.4.0
#############

    - updated dependency to Pixidos/gpwebpay-core ^2.2
    - remove support for PHP 7.x
    - remove support for Nette <= 2.4
    - add support for PHP ^8.1

Version 3.2.0
#############

BC Breaks
----------
	- remove onSuccess and onError from GPWebPayControl
	- remove handleSuccess from GPWebPayControl because GPWebPay drop support parameters in url
	- change config signature for multiple gateway see documentation
	- Interfaces renamed from ``I<name>`` to ``<name>Interface``

New Features
------------

	- add ResponseProvider see [documentation]()
	- updated dependency to Pixidos/gpwebpay-core ^2.2

Verison 3.1.0
#############

	- Move core code to framework agnostic library `Pixidos/GPWebPay-Core <https://github.com/Pixidos/gpwebpay-core>`_
	- add Pixidos/coding-standarts
	- static analyse by phpStan
	- add Nette 3.x support
	- add php 7.4 to testing metrix

