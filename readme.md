# CakePHP Authentication Bridge

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/Phauthentic/authentication-cakephp/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/Phauthentic/authentication-cakephp/)
[![Code Quality](https://img.shields.io/scrutinizer/g/Phauthentic/authentication-cakephp/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/Phauthentic/authentication-cakephp/)

This package will allow you to lookup user credentials with your CakePHP application using the [Phautentic Authentication](https://github.com/Phauthentic/authentication) library.

## How to use it

Install it via composer.

```sh
composer require phauthentic/authentication-cakephp
```

## CakePHP ORM Resolver

Configuration option setters:

* **setUserModel()**: The user model identities are located in. Default is `Users`.
* **setFinder()**: The finder to use with the model. Default is `all`.

## Copyright & License

Licensed under the [MIT license](LICENSE.txt).

* Copyright (c) [Phauthentic](https://github.com/Phauthentic)
* Copyright (c) [Cake Software Foundation, Inc.](https://cakefoundation.org)
