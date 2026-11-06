.. _installation:

==========
Installing
==========

.. _installation.requirements:

Requirements
############

GPWebPay 4.x requires PHP 8.2 or newer, below PHP 9 (``^8.2``), and is tested on PHP 8.2–8.5.
Use a PHP version that still receives security support.

GPWebPay requires:
	- the JSON extension (``ext-json``)
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

	$ composer require "pixidos/gpwebpay:^4.0"

.. _installation.nette4:

Nette 4 (development)
#####################

Nette 4 has no stable release yet. Its current Application and DI development branches (``dev-master``, alias
``4.0.x-dev``) require PHP 8.4 or newer. GPWebPay tests Application 4 with DI 4 on PHP 8.4 and 8.5, in addition to
its stable Nette 3 jobs. The GPWebPay 4.x package version does not imply that Nette Framework 4 is stable.

The verified combinations are:

.. list-table::
   :header-rows: 1

   * - Application
     - DI
     - Bootstrap
     - PHP
   * - Stable 3.x
     - Compatible stable 3.x
     - Compatible stable 3.x
     - 8.2+
   * - 4.0.x-dev
     - Stable ^3.2
     - 4.0.x-dev
     - 8.4+
   * - 4.0.x-dev
     - 4.0.x-dev
     - Absent
     - 8.4+

For Application 4 with Bootstrap, keep DI on its compatible 3.x line:

.. code-block:: bash

    composer require "pixidos/gpwebpay:^4.0" nette/application:4.0.x-dev "nette/di:^3.2" nette/bootstrap:4.0.x-dev

For Application 4 and DI 4 together, use an application that compiles its container without Bootstrap:

.. code-block:: bash

    composer require "pixidos/gpwebpay:^4.0" nette/application:4.0.x-dev nette/di:4.0.x-dev

These commands opt in only the named development packages; the project's ``minimum-stability`` can stay ``stable``.
Existing package constraints must also match the chosen combination. Development branches change without notice;
this is experimental compatibility, and a future stable Nette 4 release has not been tested.

Current limitations:

- Bootstrap 4.0-dev still requires DI ``^3.2``, so it cannot be installed with DI 4. Without Bootstrap there is no
  ``Nette\Bootstrap\Configurator``, and ``GPWebPayExtension::register()`` cannot be used. Compile the container with
  ``Nette\DI\Compiler`` and register the extension in its ``extensions`` section; see :ref:`configuration.example`.
  The DI 4 test jobs use this path. PHPStan reads the real Bootstrap source separately, without installing or
  autoloading it, to check the optional ``Configurator`` helper's type. All graphs run PHPCS and PHPStan.
- Application 4 removed ``Presenter::__construct()``. Presenters must not call ``parent::__construct()`` there;
  see :ref:`usage.request`. Keep the parent call when using Application 3.1 or 3.2, which initialize the payload in it.

Upgrading from 3.5
##################

Upgrade PHP first, then allow ``pixidos/gpwebpay:^4.0`` and GPWebPay Core ``^3.0``. Payment APIs and configuration
remain compatible with 3.5.0. Older releases retain their historical PHP requirements; this is not a commitment to
continued maintenance of those series or of PHP 8.1 applications.
