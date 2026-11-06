# GPWebPay
![](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)
[![Code Analyse](https://github.com/Pixidos/GPWebPay/actions/workflows/code_analyse.yml/badge.svg)](https://github.com/Pixidos/GPWebPay/actions/workflows/code_analyse.yml)
[![Unit Tests](https://github.com/Pixidos/GPWebPay/actions/workflows/unit_tests.yml/badge.svg)](https://github.com/Pixidos/GPWebPay/actions/workflows/unit_tests.yml)
[![Downloads](https://poser.pugx.org/pixidos/gpwebpay/downloads)](https://packagist.org/packages/pixidos/gpwebpay)
[![Latest stable](https://img.shields.io/packagist/v/pixidos/gpwebpay.svg)](https://packagist.org/packages/pixidos/gpwebpay)
[![Code coverage](https://codecov.io/gh/Pixidos/GPWebPay/branch/master/graph/badge.svg)](https://codecov.io/gh/Pixidos/gpwebpay)
[![Documentation Status](https://readthedocs.org/projects/gpwebpay/badge/?version=latest)](https://gpwebpay.readthedocs.io/en/latest/?badge=latest)

GPWebPay integrates the [GP webpay HTTP API](https://www.gpwebpay.cz/) with Nette Framework.

For applications without Nette, use [GPWebPay Core](https://github.com/Pixidos/gpwebpay-core).


Requirements
------------

| Version | PHP       | Nette support | Maintained | Note                              |
|---------|-----------|---------------|------------|-----------------------------------|
| 3.5.x   | ^8.1      | ^3.1          | Yes        | requires GPWebPay Core ^2.5.0     |
| 3.4.x   | ^8.0 [1]  | ^3.1          | Yes        | use GPWebPay-core ~2.2 [1]        |
| 3.3.x   | ^7.2, 8.0 | ^2.4, ^3.0    | No         | use GPWebPay-core ^2.0, BC breaks |
| 3.2.x   | ^7.1      | ^2.4, ^3.0    | No         | use GPWebPay-core ^2.0, BC breaks |
| 3.1.x   | ^7.1      | ^2.4, ^3.0    | No         | use GPWebPay-core ^1.0            |
| 3.0.x   | ^7.1      | ^2.3          | No         |                                   |
| 2.x     | ^5.6      | ^2.3          | No         |                                   |

[1] 3.4.1 requires GPWebPay-core ~2.4, which requires PHP ^8.1.


- [nette/di](https://github.com/nette/di) ^3.0
- [nette/application](https://github.com/nette/application) ^3.1
- [nette/utils](https://github.com/nette/utils) ^4.0
- [latte/latte](https://github.com/nette/latte) ^3.0
- [pixidos/gpwebpay-core](https://github.com/pixidos/gpwebpay-core) ^2.5.0

The exact requirements of a release are in its `composer.json`.


Installation
------------

Install GPWebPay with [Composer](https://getcomposer.org/):

```sh
$ composer require pixidos/gpwebpay
```


Documentation
------------

Learn more in the [documentation](https://gpwebpay.readthedocs.io/en/latest/).

