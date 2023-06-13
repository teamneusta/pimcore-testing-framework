# Pimcore Testing Framework

Provides tools for Pimcore unit/integration testing with PHPUnit.

## Installation

```shell
composer require --dev teamneusta/pimcore-testing-framework
```

## Usage

### Bootstrapping Pimcore

We provide a convenience method to bootstrap Pimcore for running tests.
Just call `BootstrapPimcore::bootstrap()` in your `tests/bootstrap.php` as seen below, and you're done.

```php
# tests/bootstrap.php
<?php

include dirname(__DIR__).'/vendor/autoload.php';

Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore::bootstrap();
```

There's also `BootstrapPimcore::setEnv()` to set environment variables during the bootstrap process.

> **Hint**: you can pass the application environment (`APP_ENV`) directly as a parameter of 
> `BootstrapPimcore::bootstrap()` (it defaults to `test`).

### Switch Common Behavior on/off in Test Cases

We provide traits to switch common behavior on/off in whole test case classes.

#### Admin Mode

- `WithAdminMode`
- `WithoutAdminMode`

#### Cache

- `WithoutCache`

#### Inherited Values of DataObjects

- `WithInheritedValues`
- `WithoutInheritedValues`

### Integration Tests With a Database

If you write integration tests that use the database, we've got you covered too.

We provide the `ResetDatabase` trait, which does the heavy lifting:
Just use it in one of your test case classes,
and it'll install a fresh Pimcore into the configured database before the first test is run.
It'll also reset the database between each test, so you don't have to worry about leftovers from previous tests.

#### Using a Dump

If you already have a database dump that you want to use instead of a fresh Pimcore installation,
there's the `DATABASE_DUMP_LOCATION` environment variable. 
Point it to the location of your dump, and it'll be used instead.

#### Faster Database Reset

By default, resetting the database between the tests works by dropping the database,
recreating it and reinstalling Pimcore (or reimporting the dump).

This is rather slow, but there are some tricks that can speed it up:

##### Storing the Database in the RAM

Normally, the database is stored on the disk, so that the data is persisted.
But we don't really need this for testing, so if you're using Docker, you can configure it to store it in RAM instead:

```yaml
# compose.yaml
services:
  db:
    image: 'mariadb:10.10' # or 'mysql:8.0'
    tmpfs:
      - /tmp
      - /var/lib/mysql
```

##### Wrapping Each Test in a Transaction

We support the [`dama/doctrine-test-bundle`](https://packagist.org/packages/dama/doctrine-test-bundle),
which isolates database tests by wrapping them into a transaction.
You just have to [install the bundle according to its readme](https://github.com/dmaicher/doctrine-test-bundle#how-to-install-and-use-this-bundle),
and it'll automatically be used.

## Contribution

Feel free to open issues for any bug, feature request, or other ideas.

Please remember to create an issue before creating large pull requests.
