# Pimcore Testing Framework

Provides tools for Pimcore unit/integration testing with PHPUnit.

## Installation

1.  **Require the bundle**

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

You can also pass any environment variable via named arguments to this method:

```php
# tests/bootstrap.php
Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore::bootstrap(
    APP_ENV: 'custom',
    SOMETHING: 'else',
);
```

#### Integration Tests For a Bundle

If you want to add integration tests for a Bundle, you need to set up an application with a kernel.
Pimcore also expects some configuration
(e.g., for the [`security`](https://github.com/pimcore/skeleton/blob/10.2/config/packages/security.yaml)) to be present.

You can use the `\Neusta\Pimcore\TestingFramework\Kernel\TestKernel` as a base,
which already provides all necessary configurations with default values
(see: `dist/config` and `dist/pimcore10/config` or `dist/pimcore11/config`, depending on your Pimcore version).

For a basic setup, you can use the `TestKernel` directly:

```php
# tests/bootstrap.php
<?php

use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore;

require_once __DIR__ . '/../vendor/autoload.php';

BootstrapPimcore::bootstrap(
    PIMCORE_PROJECT_ROOT: __DIR__.'/app',
    KERNEL_CLASS: TestKernel::class,
);
```

> **Note**: Don't forget to create the `tests/app` directory!
> ```shell
> mkdir -p tests/app
> echo '/var' > tests/app/.gitignore
> ```

> **Note**:
> Since the kernels of Pimcore 10 and 11 are not compatible (the signature of the method `configureContainer()` differs),
> we have extended our `TestKernel` with the ability to load separate configuration files depending on the version.
> Configuration that is compatible with both Pimcore versions belongs to the `config/` folder of the test app as before.
> Version specific configuration can be placed inside the `config/pimcore10/`
> or `config/pimcore11/` folder and will be loaded last.

### Switch Common Behavior on/off in Test Cases

We provide traits to switch common behavior on/off in whole test case classes.

#### Admin Mode

The admin mode is disabled by default when calling `BootstrapPimcore::bootstrap()`.

To enable it again, you can use the `WithAdminMode` trait.

#### Cache

- `WithoutCache`

#### Inherited Values of DataObjects

- `WithInheritedValues`
- `WithoutInheritedValues`

### Integration Tests With a Configurable Kernel

The `TestKernel` can be configured dynamically for each test.
This is useful if different configurations or dependent bundles are to be tested.
To do this, your test class must inherit from `ConfigurableKernelTestCase`:

```php
use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;

class SomeTest extends ConfigurableKernelTestCase
{
    public function test_bundle_with_different_configuration(): void
    {
        // Boot the kernel with a config closure
        $kernel = self::bootKernel(['config' => static function (TestKernel $kernel) {
            // Add some other bundles we depend on
            $kernel->addTestBundle(OtherBundle::class);

            // Add some configuration
            $kernel->addTestConfig(__DIR__.'/config.yaml');
            
            // Configure some extension
            $kernel->addTestExtensionConfig('my_bundle', ['some_config' => true]);
            
            // Add some compiler pass
            $kernel->addTestCompilerPass(new MyBundleCompilerPass());
        }]);
    }
}
```

#### Attributes

An alternative to passing a `config` closure in the `options` array to `ConfigurableKernelTestCase::bootKernel()` 
is to use attributes for the kernel configuration.

```php
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureContainer;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\RegisterBundle;
use Neusta\Pimcore\TestingFramework\Test\Attribute\RegisterCompilerPass;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;

#[RegisterBundle(SomeBundle::class)]
class SomeTest extends ConfigurableKernelTestCase 
{
    #[ConfigureContainer(__DIR__ . '/Fixtures/some_config.yaml')]
    #[ConfigureExtension('some_extension', ['config' => 'values'])]
    #[RegisterCompilerPass(new SomeCompilerPass())]
    public function test_something(): void
    {
        self::bootKernel();

        // test something
    }
}
```

> [!TIP]
> All attributes can be used on class *and* test method level.

#### Data Provider

You can also use the `RegisterBundle`, `ConfigureContainer`, `ConfigureExtension`, or `RegisterCompilerPass` classes 
to configure the kernel in a data provider.

```php
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;

class SomeTest extends ConfigurableKernelTestCase 
{
    public function provideTestData(): iterable
    {
        yield [
            'some value', 
            new ConfigureExtension('some_extension', ['config' => 'some value']),
        ];

        yield [
            new ConfigureExtension('some_extension', ['config' => 'other value']), 
            'other value',
        ];
    }

    /** @dataProvider provideTestData */
    public function test_something(string $expected): void
    {
        self::assertSame($expected, self::getContainer()->getParameter('config'));
    }
}
```

> [!TIP]
> The kernel configuration objects are *not* passed as arguments to the test method,
> which means you can use them anywhere between your provided real test data.

#### Custom Attributes

You can create your own kernel configuration attributes by implementing the `KernelConfiguration` interface:

```php
use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Neusta\Pimcore\TestingFramework\Test\Attribute\KernelConfiguration;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class ConfigureSomeBundle implements KernelConfiguration
{
    public function __construct(
        private readonly array $config,
    ) {
    }

    public function configure(TestKernel $kernel): void
    {
        $kernel->addTestBundle(SomeBundle::class);
        $kernel->addTestExtensionConfig('some', array_merge(
            ['default' => 'config'],
            $this->config,
        ));
    }
}
```

Then you can use the new class as an attribute or inside a data provider.

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

### Local Development

To develop on local machine, the vendor dependencies are required.

```shell
bin/composer install
```

We use composer scripts for our main quality tools. They can be executed via the `bin/composer` file as well.

```shell
bin/composer cs:fix
bin/composer phpstan
bin/composer tests
```
