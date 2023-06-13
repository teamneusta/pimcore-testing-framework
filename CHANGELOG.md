# Changelog

## next

### Breaking Changes:

- Admin mode will be disabled by default when bootstrapping pimcore
- `Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore::bootstrap()` now expects named arguments.<br>
  If you pass the application environment as a parameter, you now have to prefix it with: `APP_ENV:`:
  ```diff
  -BootstrapPimcore::bootstrap('something')
  +BootstrapPimcore::bootstrap(APP_ENV: 'something')
  ```
- The second parameter (`$value`) of `Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore::setEnv()`
  is now of type `string`.

### Bugfixes:

- Reset admin mode to previous state after each test class when using `WithAdminMode`/`WithoutAdminMode` traits
