.. _installation:

==========
Installing
==========

.. _installation.requirements:

Requirements
############

GPWebPay |version| requires PHP ^8.1 and is tested with PHP 8.2 to 8.5.
Use a PHP version that still receives security support.

GPWebPay requires:
	- the `ext-openssl <http://php.net/manual/en/openssl.setup.php>`_ extension
	- `pixidos/gpwebpay-core <https://github.com/pixidos/gpwebpay-core>`_ ``^2.4.3``
	- `nette/di <https://github.com/nette/di>`_ ``^3.0``
	- `nette/application <https://github.com/nette/application>`_ ``^3.1``
	- `nette/utils <https://github.com/nette/utils>`_ ``^4.0``
	- `latte/latte <https://github.com/nette/latte>`_ ``^3.0``

Composer chooses the versions of these packages that support your PHP version. The tests run with
nette/application 3.2 and 3.3 and Latte 3.1.

.. _installation.composer:

Composer
########

| Simple add a dependency on ``pixidos/gpwebpay`` to your project's ``composer.json`` file.
| If you use `Composer <https://getcomposer.org/>`_ to manage the dependencies of your project:

.. code-block:: bash

	$ composer require pixidos/gpwebpay

