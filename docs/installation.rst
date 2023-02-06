.. _installation:

==========
Installing
==========

.. _installation.requirements:

Requirements
############

GPWebPay |version| requires PHP ^8.0 using the latest version of PHP is highly
recommended.

GPWebPay requires:
	- the `ext-openssl <http://php.net/manual/en/openssl.setup.php>`_ extensions
	- `pixidos/gpwebpay-core <https://github.com/pixidos/gpwebpay-core>`_ ``~2.0``
	- `nette/di <https://github.com/nette/di>`_ ``^2.4.10 || ^3.0``
	- `nette/application <https://github.com/nette/application>`_ ``^2.4.10 || ^3.0``
	- `nette/utils <https://github.com/nette/utils>`_ ``^2.5 || ^3.0``
	- `latte/latte <https://github.com/nette/latte>`_ ``^2.4``

.. _installation.composer:

Composer
########

| Simple add a dependency on ``pixidos/gpwebpay`` to your project's ``composer.json`` file.
| If you use `Composer <https://getcomposer.org/>`_ to manage the dependencies of your project:

.. code-block:: bash

	$ composer require pixidos/gpwebpay


