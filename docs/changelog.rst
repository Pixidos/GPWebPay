.. _changelog:

=========
Changelog
=========

Unreleased
##########

Proposed as version 4.0.0, because PHP 8.1 is no longer supported.

Breaking changes:

    - Requires PHP ^8.2. PHP 8.1 no longer receives security fixes. Projects that still run on PHP 8.1 can stay on 3.5.x.
    - Requires pixidos/gpwebpay-core ^3.0, which requires PHP ^8.2 and has the same API and behaviour as 2.5.

Changes:

    - Accepts nette/di 4.0 (``^3.1 || ^4.0``); nette/application already accepted 4.0. nette/di 3.0 could not be installed
      with nette/utils 4 before either. Nette 4 has not been released:
      the development branches of nette/application and nette/di (4.0.x-dev) require PHP 8.4 and are installed only
      when a project requires them, see :ref:`installation.nette4`. By default Composer installs stable Nette 3, as before.
    - The API, the configuration and the processing of payments and responses are unchanged. ``GPWebPayExtension`` registers
      the same services under the same names; with nette/di 4 it uses ``ContainerBuilder::add()``, with nette/di 3.2
      ``addDefinition()``, and it sets their creators with ``setCreator()`` instead of ``setFactory()``, which nette/di 4 deprecates.
    - Documentation: requirements, Nette 4 development branches and their current limitations.
    - Development: CI tests stable Nette 3 on PHP 8.2 to 8.5, and nette/application with nette/di 4.0.x-dev on PHP 8.4
      and 8.5, including PHPCS and PHPStan. Without nette/bootstrap, whose development branch still requires nette/di 3,
      the tests compile the container with ``Nette\DI\Compiler``, and PHPStan reads ``Nette\Bootstrap\Configurator`` from
      the downloaded source of nette/bootstrap 4.0.x-dev, which is not installed.

Version 3.5.0
#############

Fixes:

    - ``GPWebPayControl`` can be rendered more than once, for example to show the payment button in two places on a page.
      Previously, the second ``render()`` threw "The variable 'checkoutLink' already exists".
    - The usage example reads a GP webpay response from the form fields of a ``POST`` and from the query of a ``GET``;
      presenter parameters do not contain the form fields, so ``POST`` responses failed. It handles errors with
      ``GPWebPayException``, stops on invalid responses, processes a payment attempt only once and uses a stored unique
      payment attempt instead of ``time()`` as ORDERNUMBER.
    - Requires pixidos/gpwebpay-core ^2.5.0: requests use the selected gateway's response URL,
      responses containing ACRC, RRN, PAR, TRACEID or an MD suffix pass signature verification, and gateway keys are case-insensitive.

Changes:

    - Configuration is validated when the container is compiled. Missing or unknown attributes, an empty configuration,
      a ``depositFlag`` other than 0 or 1, or a non-string value for a string attribute cause
      ``Nette\DI\InvalidConfigurationException`` with the attribute's path. Previously, these errors were ignored or
      failed when a service was first used. Quote numeric ``merchantNumber`` and ``privateKeyPassphrase`` values.
    - PHP ^8.1, as required by pixidos/gpwebpay-core 2.4.
    - Documentation: requirements, GP webpay HTTP API 1.19 gateway addresses, configuration validation and gateway keys.
    - Development: tests run with nette/application 3.3, CI tests PHP 8.2 to 8.5 and runs ``composer audit``,
      the unused abandoned nette/finder was removed.

Version 3.4.1
#############

    - Requires pixidos/gpwebpay-core ~2.4.
    - ``depositFlag`` is optional with one or multiple gateways; the default is 1.
    - When ``defaultGateway`` is omitted, the default gateway is ``default``.
    - Moved nette/bootstrap to require-dev.

Version 3.4.0
#############

    - Updated the dependency on pixidos/gpwebpay-core to ^2.2.
    - Removed support for PHP 7.x.
    - Removed support for Nette <= 2.4.
    - Added support for PHP ^8.1.

Version 3.2.0
#############

Breaking changes
----------------
	- Removed ``onSuccess`` and ``onError`` from ``GPWebPayControl``.
	- Removed ``handleSuccess`` because GP webpay no longer supported parameters in the response URL.
	- Changed the configuration signature for multiple gateways; see :ref:`configuration`.
	- Renamed interfaces from ``I<name>`` to ``<name>Interface``.

New Features
------------

	- Added ``ResponseProvider``; see :ref:`usage.response`.
	- Updated the dependency on pixidos/gpwebpay-core to ^2.2.

Version 3.1.0
#############

	- Moved core code to the framework-independent `GPWebPay Core library <https://github.com/Pixidos/gpwebpay-core>`_.
	- Added pixidos/coding-standards.
	- Added static analysis with PHPStan.
	- Added Nette 3.x support.
	- Added PHP 7.4 to the test matrix.

