# Changelog

## v0.15.0
### Breaking Changes:
- Pimcore Asset/Document/DataObject versioning is now disabled by default in
  `Neusta\Pimcore\TestingFramework\BootstrapPimcore::bootstrap()`

### Features:
- Add attribute-driven Pimcore configuration via the
  `Neusta\Pimcore\TestingFramework\ConfigurablePimcore` trait and the
  `Neusta\Pimcore\TestingFramework\Attribute\Pimcore\{AdminMode,Cache,RuntimeCache,DataObjectInheritance,Versioning}`
  attributes, which work on class *and* test method level and restore the previous state afterwards
- Add `Neusta\Pimcore\TestingFramework\PimcoreConfiguration` for custom Pimcore configuration attributes

### Changes:
- Moved `Neusta\Pimcore\TestingFramework\Kernel\TestKernel`
  to `Neusta\Pimcore\TestingFramework\TestKernel`
- Moved `Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore`
  to `Neusta\Pimcore\TestingFramework\BootstrapPimcore`
- Moved `Neusta\Pimcore\TestingFramework\Database\ResetDatabase`
  to `Neusta\Pimcore\TestingFramework\ResetDatabase`
- Moved `Neusta\Pimcore\TestingFramework\Test\Attribute\KernelConfiguration`
  to `Neusta\Pimcore\TestingFramework\KernelConfiguration`
- Moved `Neusta\Pimcore\TestingFramework\Test\Attribute\{ConfigureContainer,ConfigureExtension,ConfigureRoute,RegisterBundle,RegisterCompilerPass}`
  to `Neusta\Pimcore\TestingFramework\Attribute\Kernel\{...}`
- Moved the remaining, `@internal` classes of `Neusta\Pimcore\TestingFramework\Database`
  to `Neusta\Pimcore\TestingFramework\Internal\Database`

### Deprecations:
- Deprecated `Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase` and
  `Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase`
  in favor of the `Neusta\Pimcore\TestingFramework\ConfigurableKernel` trait
- Deprecated the `Neusta\Pimcore\TestingFramework\Pimcore\{WithAdminMode,WithoutCache,WithInheritedValues,WithoutInheritedValues}`
  traits in favor of the `Attribute\Pimcore\*` attributes
- All moved classes listed above are still available under their old names and emit a deprecation

See [UPGRADE-0.15.md](UPGRADE-0.15.md) for the full migration guide.

## v0.14.0
### Changes:
- Add support for Pimcore 2026 and PHP 8.5
- Split `PimcoreInstaller` into `LegacyPimcoreInstaller`, `PimcoreDatabaseInstaller` and `SqlDumpImporter`
- Add `Pimcore\PlatformVersion` to determine the installed Pimcore major version

## v0.13.5
### Changes:
- Add extensive unit and functional test coverage

## v0.13.4
### Changes:
- Support PHPUnit 11

## v0.13.3
### Features:
- Load environment variables using Symfony Dotenv during Pimcore bootstrap
- Add `DoctrineSchemaAssetFilter` to scope schema operations to the tables managed by a given entity manager

## v0.13.2
### Changes:
- Upgrade PHPStan to 2.x

### Bugfixes:
- Make `TestKernel::configureContainer()` compatible with Symfony 7.4.9

## v0.13.1
### Changes:
- Support PHPUnit 10

## v0.13.0
### Changes:
- Add support for Pimcore 12 and drop support for Pimcore <11.5

## v0.12.11
### Changes:
- fix(installer): importing last chunk of SQL dump

## v0.12.10
### Changes:
- Add `ConfigurableWebTestcase`
- Add support for dynamic route configuration in `TestKernel`

## v0.12.9
### Changes:
- Allow Pimcore 11.5
- Drop support for Pimcore 10.5
- Create distinct cache directories per dynamic kernel test configuration

## v0.12.8
### Changes:
- Allow PHP 8.3

## v0.12.7
### Changes:
- Allow Pimcore 11.3 and 11.4

## v0.12.6
### Bugfixes:
- Fix importing database dumps on Alpine Linux

## v0.12.5
### Changes:
- Remove unnecessary `doctrine/annotations` from test suite

## v0.12.4
### Bugfixes:
- Fix type definition for `ConfgureExtension`

## v0.12.3
### Features:
- Add support for Pimcore 11.2

## v0.12.2
### Features:
- Allow kernel configuration via data provider

## v0.12.1
### Features:
- Allow kernel configuration via attributes

## v0.12.0
### Features:
- Dynamically configurable test kernel with which you can register bundles, load configurations,
  configure extensions, and register compiler passes for each test.

### Changes:
- Mark Pimcore 11.2 as incompatible.

## v0.11.1
### Features:
- Support for Pimcore 11.

## v0.11.0
### Breaking Changes:
- `Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore::bootstrap()` now expects named arguments.<br>
  If you pass the application environment as a parameter, you now have to prefix it with: `APP_ENV:`:
  ```diff
  -BootstrapPimcore::bootstrap('something')
  +BootstrapPimcore::bootstrap(APP_ENV: 'something')
  ```
- The second parameter (`$value`) of `Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore::setEnv()`
  is now of type `string`.
- Admin mode will be disabled by default when bootstrapping pimcore.
- The `WithoutAdminMode` trait was removed.

### Bugfixes:
- Reset admin mode to previous state after each test class when using `WithAdminMode` trait.

### Changes:
- Drop support for Pimcore `<10.5` and PHP `<8.1`.
