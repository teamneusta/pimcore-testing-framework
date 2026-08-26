# Pimcore Testing Framework

Provides tools for Pimcore unit/integration testing with PHPUnit.

## Installation

1.  **Require the bundle**

    ```shell
    composer require --dev teamneusta/pimcore-testing-framework
    ```

## Usage

### Bootstrapping Pimcore

This Bundle provides a convenience method to bootstrap Pimcore for running tests:
Call `BootstrapPimcore::bootstrap()` in your `tests/bootstrap.php` as seen below, and you’re done.

```php
# tests/bootstrap.php
require dirname(__DIR__).'/vendor/autoload.php';

\Neusta\Pimcore\TestingFramework\BootstrapPimcore::bootstrap();
```

You can also pass any environment variable via named arguments to this method:

```php
# tests/bootstrap.php
\Neusta\Pimcore\TestingFramework\BootstrapPimcore::bootstrap(
    APP_ENV: 'custom',
    SOMETHING: 'else',
);
```

> [!NOTE]
> Make sure this file is configured as the bootstrap file in your `phpunit.xml.dist` file:
> ```xml
> <!-- phpunit.xml.dist -->
> <?xml version="1.0" encoding="UTF-8" ?>
> <phpunit
>     bootstrap="tests/bootstrap.php"
> >
>     <!-- ... -->
> </phpunit>
> ```

#### Integration Tests for a Bundle

If you want to add integration tests for a Bundle, you need to set up an application with a kernel.
Pimcore also expects some configuration
(e.g., for the [`security`](https://github.com/pimcore/skeleton/blob/11.0/config/packages/security.yaml)) to be present.

You can use the `\Neusta\Pimcore\TestingFramework\TestKernel` as a base,
which already provides all necessary configurations with default values
(see: `dist/config` and `dist/pimcore11/config`, `dist/pimcore12/config` or `dist/pimcore2026/config`,
depending on your Pimcore version).

For a basic setup, you can use the `TestKernel` directly:

```php
# tests/bootstrap.php
use Neusta\Pimcore\TestingFramework\BootstrapPimcore;
use Neusta\Pimcore\TestingFramework\TestKernel;

require dirname(__DIR__).'/vendor/autoload.php';

BootstrapPimcore::bootstrap(
    PIMCORE_PROJECT_ROOT: __DIR__.'/app',
    KERNEL_CLASS: TestKernel::class,
);
```

> [!IMPORTANT]  
> Remember to create the `tests/app` directory!
> ```shell
> mkdir -p tests/app
> echo '/var' > tests/app/.gitignore
> ```

> [!NOTE]
> The supported Pimcore versions expect different configuration, so the `TestKernel` can load separate
> configuration files depending on the version it runs against.
> Configuration that is compatible with every supported version belongs to the `config/` folder of the
> test app as before. Version specific configuration can be placed inside `config/pimcore11/`,
> `config/pimcore12/` or `config/pimcore2026/` and will be loaded last.

### Switch Common Behavior On/Off in Test Cases

Pimcore keeps a lot of its behavior in global state. This bundle lets you flip that state per test case
or per test method with attributes, and restores the previous value afterwards.

Add the `ConfigurablePimcore` trait to your test case, then annotate the class or the test method:

```php
use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\AdminMode;
use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\DataObjectInheritance;
use Neusta\Pimcore\TestingFramework\ConfigurablePimcore;
use PHPUnit\Framework\TestCase;

#[AdminMode]
class SomeTest extends TestCase
{
    use ConfigurablePimcore;

    public function test_something_in_admin_mode(): void
    {
        // ...
    }

    #[AdminMode(false)]
    #[DataObjectInheritance(false)]
    public function test_something_else(): void
    {
        // ...
    }
}
```

All attributes live in `Neusta\Pimcore\TestingFramework\Attribute\Pimcore` and take a single
`bool $enable` that defaults to `true`:

| Attribute | Switches |
|---|---|
| `AdminMode` | Pimcore’s admin mode — which also flips hidden/unpublished visibility and inherited/fallback values together |
| `Cache` | `Pimcore\Cache` |
| `RuntimeCache` | `Pimcore\Cache\RuntimeCache` |
| `DataObjectInheritance` | `DataObject::setGetInheritedValues()` |
| `Versioning` | `Pimcore\Model\Version` |

> [!TIP]
> Attributes work on class *and* test method level. For a given test, a method-level attribute wins over
> the class-level one, and both are reset afterwards.

> [!NOTE]
> Every attribute except `Cache` works on a plain `PHPUnit\Framework\TestCase`.
> `Cache` needs a booted kernel, so it requires a `KernelTestCase`.

> [!IMPORTANT]
> `BootstrapPimcore::bootstrap()` disables admin mode *and* versioning by default.
> Use `#[Versioning]` to switch versioning back on for a single test or test case.

#### Custom Attributes

You can write your own by implementing the `PimcoreConfiguration` interface:

```php
use Neusta\Pimcore\TestingFramework\PimcoreConfiguration;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class MaintenanceMode implements PimcoreConfiguration
{
    private bool $wasEnabled;

    public function __construct(
        private readonly bool $enable = true,
    ) {
    }

    /** Whether `apply()` and `reset()` need a booted kernel. */
    public static function requiresBootedKernel(): bool
    {
        return false;
    }

    public function apply(): void
    {
        $this->wasEnabled = SomeApi::isEnabled();

        SomeApi::setEnabled($this->enable);
    }

    public function reset(): void
    {
        SomeApi::setEnabled($this->wasEnabled);
    }
}
```

> [!IMPORTANT]
> Keep the backup in an *instance* property, as above. The same attribute may appear on the class and on
> the test method, and each instance has to restore what it saw itself.

<details>
<summary>Deprecated: the <code>With*</code> traits</summary>

Before 0.15 these switches were traits that applied to a whole test case class: `WithAdminMode`,
`WithoutCache`, `WithInheritedValues` and `WithoutInheritedValues`. They still work but are deprecated —
see [UPGRADE-0.15.md](UPGRADE-0.15.md) for the replacements.
</details>

### Integration Tests With a Configurable Kernel

The `TestKernel` can be configured dynamically for each test.
This is useful if different configurations or dependent bundles are to be tested.
To do this, your test class must use the `ConfigurableKernel` trait:

```php
use Neusta\Pimcore\TestingFramework\ConfigurableKernel;
use Neusta\Pimcore\TestingFramework\TestKernel;
use Pimcore\Test\KernelTestCase;

class SomeTest extends KernelTestCase
{
    use ConfigurableKernel;

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

            // Add some routes
            $kernel->addTestRoute(__DIR__.'/routes.yaml');

            // Add some compiler pass
            $kernel->addTestCompilerPass(new MyBundleCompilerPass());
        }]);
    }
}
```

#### Attributes

An alternative to passing a `config` closure in the `options` array to `KernelTestCase::bootKernel()` 
is to use attributes for the kernel configuration.

```php
use Neusta\Pimcore\TestingFramework\Attribute\Kernel\ConfigureContainer;
use Neusta\Pimcore\TestingFramework\Attribute\Kernel\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Attribute\Kernel\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Attribute\Kernel\RegisterBundle;
use Neusta\Pimcore\TestingFramework\Attribute\Kernel\RegisterCompilerPass;
use Neusta\Pimcore\TestingFramework\ConfigurableKernel;
use Pimcore\Test\KernelTestCase;

#[RegisterBundle(SomeBundle::class)]
class SomeTest extends KernelTestCase 
{
    use ConfigurableKernel;

    #[ConfigureContainer(__DIR__ . '/Fixtures/some_config.yaml')]
    #[ConfigureExtension('some_extension', ['config' => 'values'])]
    #[ConfigureRoute(__DIR__ . '/Fixtures/routes.yaml')]
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


You can also use the `ConfigureContainer`, `ConfigureExtension`, `ConfigureRoute`, `RegisterBundle`, or
`RegisterCompilerPass` classes to configure the kernel in a data provider.

```php
use Neusta\Pimcore\TestingFramework\Attribute\Kernel\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\ConfigurableKernel;
use Pimcore\Test\KernelTestCase;

class SomeTest extends KernelTestCase 
{
    use ConfigurableKernel;

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
use Neusta\Pimcore\TestingFramework\KernelConfiguration;
use Neusta\Pimcore\TestingFramework\TestKernel;

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

If you write integration tests that use the database, we’ve got you covered too.

This bundle provides the `ResetDatabase` trait, which does the heavy lifting:
Use it in one of your test case classes,
and it will install a fresh Pimcore into the configured database before the first test is run.
It will also reset the database between each test, so you don’t have to worry about leftovers from previous tests.

```php
use Neusta\Pimcore\TestingFramework\ResetDatabase;
use Pimcore\Test\KernelTestCase;

class SomeDatabaseTest extends KernelTestCase
{
    use ResetDatabase;
}
```

#### Using a Dump

If you already have a database dump that you want to use instead of a fresh Pimcore installation,
there’s the `DATABASE_DUMP_LOCATION` environment variable.
Point it to the location of your dump, and it will be used instead.

#### Faster Database Reset

By default, resetting the database between the tests works by dropping the database,
recreating it and reinstalling Pimcore (or reimporting the dump).

This is rather slow, but there are some tricks that can speed it up:

##### Storing the Database in the RAM

Normally, the database is stored on the disk, so that the data is persisted.
But we don’t really need this for testing, so if you’re using Docker, you can configure it to store it in RAM instead:

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
[Install the bundle according to its readme](https://github.com/dmaicher/doctrine-test-bundle#how-to-install-and-use-this-bundle),
and it will automatically be used.

## Contribution

Feel free to open issues for any bug, feature request, or other ideas.

Please remember to create an issue before creating large pull requests.

### Local Development

To develop on your local machine, instance identification for Pimcore 12 and newer is needed.

Copy the `compose.override.yaml.dist` file to `compose.override.yaml`:

```shell
cp -n compose.override.yaml.dist compose.override.yaml
```

And replace all `replace_with_secret` values with your data.

Then install the dependencies:

```shell
bin/composer install
```

We use composer scripts for our main quality tools. They can be executed via the `bin/composer` file as well.

```shell
bin/composer cs:fix
bin/composer phpstan
bin/composer dependencies:check
```

For the tests there is a different script, that includes a database setup.

```shell
bin/run-tests
```

This library supports several Pimcore versions at once. To develop against a specific one, use:

```shell
bin/switch-pimcore-version 11   # or 12, or 2026
```

Against Pimcore 2026, PHPStan needs its own configuration:

```shell
bin/composer phpstan -- -c phpstan-2026.neon
```
