# Changelog

## v0.13.0
### Features:
- Provide a `WebTestCase` with configurable kernel

### Changes:
- Deprecated `Neusta\Pimcore\TestingFramework\Kernel\TestKernel` 
  in favor of `Neusta\Pimcore\TestingFramework\TestKernel`
- Deprecated `Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore` 
  in favor of `Neusta\Pimcore\TestingFramework\BootstrapPimcore`
- Deprecated `Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase` 
  in favor of `Neusta\Pimcore\TestingFramework\KernelTestCase`
- Deprecated `Neusta\Pimcore\TestingFramework\Test\Attribute\KernelConfiguration` 
  in favor of `\Neusta\Pimcore\TestingFramework\Attribute\ConfigureKernel`
- Deprecated `Neusta\Pimcore\TestingFramework\Test\Attribute\{ConfigureContainer,ConfigureExtension,RegisterBundle,RegisterCompilerPass}` 
  in favor of `\Neusta\Pimcore\TestingFramework\Attribute\Kernel\{ConfigureContainer,ConfigureExtension,RegisterBundle,RegisterCompilerPass}`

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
