# Changelog

## next

### Breaking Changes:

- `Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore::bootstrap()` now expects named arguments.<br>
  If you pass the application environment as a parameter, you now have to prefix it with: `APP_ENV:`:
  ```diff
  -BootstrapPimcore::bootstrap('something')
  +BootstrapPimcore::bootstrap(APP_ENV: 'something')
  ```
- The second parameter (`$value`) of `Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore::setEnv()`
  is now of type `string`.
