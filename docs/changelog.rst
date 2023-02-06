.. _changelog:

=========
Changelog
=========

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

