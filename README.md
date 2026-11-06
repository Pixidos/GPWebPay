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

This README describes **GPWebPay 4.x**, the current package series. It requires **PHP 8.2 or newer (below PHP 9)**,
OpenSSL and JSON, and GPWebPay Core `^3.0`. Stable Nette 3 is the default; the GPWebPay package version does not mean
that stable Nette Framework 4 is available.

- [nette/di](https://github.com/nette/di) `^3.1 || ^4.0`
- [nette/application](https://github.com/nette/application) `^3.1 || ^4.0`
- [nette/utils](https://github.com/nette/utils) `^4.0`
- [latte/latte](https://github.com/nette/latte) `^3.0`
- [pixidos/gpwebpay-core](https://github.com/Pixidos/gpwebpay-core) `^3.0`

Use a PHP release that receives security updates. CI tests the stable dependency graph on PHP 8.2–8.5.
Composer selects compatible versions: the tested graph uses Nette Application 3.2 on PHP 8.2 and 3.3 on PHP 8.3–8.5.

Installation
------------

Install with [Composer](https://getcomposer.org/):

```sh
composer require "pixidos/gpwebpay:^4.0"
```

For an existing installation, upgrade PHP first and update your project's GPWebPay constraint to `^4.0`.
The payment APIs and configuration remain compatible with 3.5.0. Composer also upgrades GPWebPay Core to 3.x.

Nette 4 development compatibility
---------------------------------

Nette 4 has no stable release yet. Its current Application and DI development branches require **PHP 8.4 or newer**.
Only the combinations below have been verified; future development commits and the eventual stable release may differ.

| Application | DI | Bootstrap | PHP | Registration |
| --- | --- | --- | --- | --- |
| Stable 3.x | Compatible stable 3.x | Compatible stable 3.x | 8.2+ | `Configurator` or the `extensions:` section |
| `4.0.x-dev` | Stable `^3.2` | `4.0.x-dev` | 8.4+ | `Configurator` or the `extensions:` section |
| `4.0.x-dev` | `4.0.x-dev` | Absent | 8.4+ | Compile with `Nette\DI\Compiler` and use the `extensions:` section |

To try Application 4 with Bootstrap, keep DI on its compatible 3.x line:

```sh
composer require "pixidos/gpwebpay:^4.0" nette/application:4.0.x-dev "nette/di:^3.2" nette/bootstrap:4.0.x-dev
```

To test both Application 4 and DI 4 in an application that compiles its container without Bootstrap:

```sh
composer require "pixidos/gpwebpay:^4.0" nette/application:4.0.x-dev nette/di:4.0.x-dev
```

The current Bootstrap development branch still requires DI `^3.2`, so it cannot be combined with DI 4.
Without Bootstrap there is no `Configurator`, and `GPWebPayExtension::register()` cannot be used. The DI 4 CI jobs
check this combination without installing Bootstrap; PHPStan reads its real source separately for the optional helper's type.
Both stable and development jobs run tests, PHPCS and PHPStan.

These commands opt in only the named development packages; the project's `minimum-stability` can stay `stable`.
Existing constraints on Bootstrap or other packages must also match the chosen combination. Development branches may
break without notice; treat this as experimental compatibility. See the
[Nette 4 development guide](https://gpwebpay.readthedocs.io/en/latest/installation.html#nette-4-development)
for container registration and presenter migration notes.

Previous releases
-----------------

The entries below record historical compatibility, not a promise of continued maintenance. New work targets the 4.x series.
Older releases and their tags are unchanged; use their tagged `composer.json` for exact requirements.

| Release | Historical PHP requirement | Framework compatibility | Core requirement |
| --- | --- | --- | --- |
| 3.5.0 | `^8.1` | Stable Nette 3; Nette 4 was untested | `^2.5.0` |
| 3.4.1 | Effective minimum 8.1 through Core | Nette `^3.1` | `~2.4` |
| 3.4.0 | `^8.0`, subject to the selected Core version | Nette `^3.1` | `~2.2` |
| 3.3.x | `^7.2`, 8.0 | Nette `^2.4`, `^3.0` | `^2.0` |
| 3.2.x | `^7.1` | Nette `^2.4`, `^3.0` | `^2.0` |
| 3.1.x | `^7.1` | Nette `^2.4`, `^3.0` | `^1.0` |
| 3.0.x | `^7.1` | Nette `^2.3` | See the tagged release |
| 2.x | `^5.6` | Nette `^2.3` | See the tagged release |

PHP 8.1 no longer receives security updates. Historical compatibility with it is not a recommendation to keep using it.


Documentation
------------

Learn more in the [documentation](https://gpwebpay.readthedocs.io/en/latest/).

