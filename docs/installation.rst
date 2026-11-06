.. _installation:

==========
Installing
==========

.. _installation.requirements:

Requirements
############

GPWebPay |version| requires PHP ^8.2 and is tested with PHP 8.2 to 8.5.
Use a PHP version that still receives security support.

GPWebPay requires:
	- the `ext-openssl <http://php.net/manual/en/openssl.setup.php>`_ extension
	- `pixidos/gpwebpay-core <https://github.com/pixidos/gpwebpay-core>`_ ``^3.0``
	- `nette/di <https://github.com/nette/di>`_ ``^3.1 || ^4.0``
	- `nette/application <https://github.com/nette/application>`_ ``^3.1 || ^4.0``
	- `nette/utils <https://github.com/nette/utils>`_ ``^4.0``
	- `latte/latte <https://github.com/nette/latte>`_ ``^3.0``

Composer chooses the versions of these packages that support your PHP version and installs stable Nette 3. The tests
run with nette/application 3.2 and 3.3 and Latte 3.1.

.. _installation.composer:

Composer
########

Install the library with `Composer <https://getcomposer.org/>`_:

.. code-block:: bash

	$ composer require pixidos/gpwebpay

.. _installation.nette4:

Nette 4 (development)
#####################

Nette 4 has not been released. nette/application and nette/di 4.0 exist only as development branches
(``dev-master``, alias ``4.0.x-dev``), which require PHP 8.4 or newer. GPWebPay accepts them, and its CI runs the
tests with both of them together on PHP 8.4 and 8.5. With the default ``minimum-stability: stable``, Composer installs
them only when your project requires them.

To try them, require both development branches in your project; only these two packages are then installed from
development branches, and ``minimum-stability`` stays ``stable``:

.. code-block:: bash

	$ composer require pixidos/gpwebpay nette/application:4.0.x-dev nette/di:4.0.x-dev

Development branches change without notice. Do not use them in production. A future stable Nette 4 release has not
been tested.

Known limitations of the current development branches:

- The development branch of nette/bootstrap still requires nette/di ^3.2, so it cannot be installed with nette/di
  4.0-dev. Without nette/bootstrap there is no ``Nette\Bootstrap\Configurator`` and you cannot call
  ``GPWebPayExtension::register()``. Compile the container with ``Nette\DI\Compiler`` and register the extension in
  the ``extensions`` section, see :ref:`configuration.example`.
- nette/application 4.0 removed ``Presenter::__construct()``. Presenters must not call ``parent::__construct()``
  there, see :ref:`usage.request`.
