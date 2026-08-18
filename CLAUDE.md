# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A PHP library (`teamneusta/pimcore-testing-framework`) that provides traits, attributes and base
infrastructure for unit, integration, and functional testing of Pimcore/Symfony bundles with PHPUnit.
It is consumed by other Pimcore bundle projects as a dev dependency, not run as a standalone
application. `src/` is the shipped library code; `tests/` (including `tests/app`, a minimal Pimcore
application) exercises the library against a real Pimcore kernel and database.

The library supports Pimcore 11.5, 12 and 2026, PHP 8.1–8.5, and PHPUnit 9, 10 and 11 simultaneously —
keep that compatibility matrix in mind when adding code (see `.github/workflows/tests.yaml` for the
exact matrix). Code that differs per version is the main source of bugs here; see "Version-conditional
code" below.

## Development environment

Development happens inside Docker via `compose.yaml`. Before first use, copy
`compose.override.yaml.dist` to `compose.override.yaml` and fill in real Pimcore license/instance
values (needed because Pimcore 12+ requires instance identification even for CI-like runs).

```shell
cp -n compose.override.yaml.dist compose.override.yaml
bin/composer install       # composer install inside the php container
```

`bin/composer` wraps `docker compose run` — use it instead of calling `composer` directly unless you
know PHP/composer are available locally with the right version.

## Common commands

```shell
bin/composer cs:fix               # fix code style (php-cs-fixer)
bin/composer cs:check             # check code style without fixing
bin/composer phpstan              # static analysis (level 8) - Pimcore 11/12 only
bin/composer phpstan -- -c phpstan-2026.neon   # the same, against Pimcore ^2026.1
bin/composer dependencies:check   # composer-dependency-analyser
bin/run-tests                     # full test run incl. database setup/teardown
bin/switch-pimcore-version 11|12|2026   # resolve a specific Pimcore version locally
```

`bin/run-tests` auto-detects the installed PHPUnit major version and picks `phpunit.xml.dist` (v10/v11)
or `phpunit9.xml.dist` (v9) accordingly, then tears the compose stack down afterward.

To run a single test or a subset directly inside the container (faster than `bin/run-tests` when the DB
is already up):

```shell
docker compose run --rm php vendor/bin/phpunit --filter=TestClassOrMethodName
docker compose run --rm php vendor/bin/phpunit tests/Functional/ResetDatabaseTest.php
```

Test config lives in `phpunit.xml.dist` (bootstraps via `tests/bootstrap.php`);
`beStrictAboutOutputDuringTests` and `failOnWarning` are enabled, `failOnDeprecation` deliberately is
not — the library emits its own deprecations from the BC layer. The `dama/doctrine-test-bundle` PHPUnit
extension is registered so DB transactions can wrap tests when that bundle is installed by a consumer.

### Version-conditional code

`bin/switch-pimcore-version` narrows `composer.json` locally, resolves, then restores `composer.json`
via git. Two consequences worth knowing:

- Running a plain `bin/composer update` afterwards drops `pimcore/admin-ui-classic-bundle` again (it is
  only added by the switcher, with `--no-update`), which breaks the kernel with
  "non-existent parameter `pimcore_admin_bundle.firewall_settings`". Re-add it before updating.
- `dependencies:check` only makes sense with the highest dependency set installed. On PHPUnit 9 it
  reports `PHPUnit\Framework\Attributes\*` as unknown classes, because those simply do not exist there.

## Architecture

The namespace layout follows one rule: **the root namespace holds what a consumer writes in their test
case (the traits) plus the two entry points; sub-namespaces hold supporting machinery. `Internal\` is
the authoritative marker for non-public code, with `@internal` in the doc-comment as reinforcement.**

```
Neusta\Pimcore\TestingFramework\
├── TestKernel                 Entry point: the Pimcore kernel used in tests
├── BootstrapPimcore           Entry point: called from a consumer's tests/bootstrap.php
├── ConfigurableKernel         Trait: per-test kernel configuration
├── ConfigurablePimcore        Trait: per-test Pimcore state configuration
├── ResetDatabase              Trait: database lifecycle
├── KernelConfiguration        Interface for custom kernel attributes
├── PimcoreConfiguration       Interface for custom Pimcore attributes
├── Attribute\Kernel\*         The kernel attributes
├── Attribute\Pimcore\*        The Pimcore attributes
├── Internal\*                 Machinery, including Internal\Database\*
├── Exception\*
├── Pimcore\{AdminMode, PlatformVersion}   Public wrappers around Pimcore statics
└── (deprecated BC layer, see below)
```

### Bootstrapping a Pimcore kernel for tests

- `BootstrapPimcore::bootstrap()` — the entry point consumers call from their `tests/bootstrap.php`.
  Sets env vars (default `APP_ENV=test`), loads `.env` via Symfony Dotenv if present, calls Pimcore's
  own `Bootstrap::bootstrap()`, then disables admin mode **and versioning**. Named arguments become
  extra env vars.
- `TestKernel` — a `Pimcore\Kernel` subclass designed to be configured *per test* rather than once
  globally. Consumers point `PIMCORE_KERNEL_CLASS` at it. Key mechanics:
  - `addTestBundle()`, `addTestConfig()`, `addTestRoute()`, `addTestExtensionConfig()`,
    `addTestCompilerPass()` register dynamic configuration and flip `dynamicCache = true`.
  - When `dynamicCache` is true, `getCacheDir()` appends a hash of the test-specific config
    (`getTestConfigHash()`) so each distinct dynamic configuration gets its own DI container cache
    directory instead of colliding with — or invalidating — other tests' caches.
  - `configureContainer()` takes `?LoaderInterface` / `?ContainerBuilder` because Symfony 7.4.9 reduced
    the parent signature to one parameter. It imports `dist/config/*.yaml` and
    `dist/pimcore{N}/config/*.yaml`, with `N` from `Pimcore\PlatformVersion::getMajor()`, before
    delegating to the parent and then to consumer-provided `testConfigs`.
  - `registerCoreBundlesToCollection()` adds `PimcoreAdminBundle` only `if (class_exists(...))` — the
    bundle is required on Pimcore 11/12 but not installable alongside 2026.

### Per-test configuration

Two independent traits, both built on the same collection layer:

- `ConfigurableKernel` (`@mixin KernelTestCase`) — overrides `createKernel()` and hooks `@before`/
  `@after`. Kernel configuration can come from three places, all funnelling into `KernelConfiguration`:
  1. `bootKernel(['config' => function (TestKernel $kernel) { ... }])`
  2. attributes in `Attribute\Kernel\` (`RegisterBundle`, `ConfigureContainer`, `ConfigureExtension`,
     `ConfigureRoute`, `RegisterCompilerPass`) on the class or the test method
  3. values yielded from a data provider that implement `KernelConfiguration` — these are filtered out
     of the arguments actually passed to the test method
- `ConfigurablePimcore` (`@mixin TestCase`) — applies `Attribute\Pimcore\*` attributes and resets them
  afterwards. Works on a plain `TestCase`; only attributes whose `requiresBootedKernel()` is true need
  a `KernelTestCase`.

Supporting machinery:

- `Internal\AttributeProvider` — collects attributes from the class hierarchy (parents first, stopping
  at `TOPMOST_TEST_CASES`), then the test method, then the provided data. Class-level attributes are
  **cached per test class and therefore shared across methods** — that is only safe because `apply()`
  re-reads the current state each time. It also contains the PHPUnit 9-vs-10+ switch for
  `getName()`/`name()` and `getProvidedData()`/`providedData()`.
- `Internal\KernelConfigurator` — collects eagerly in `@before`, applies lazily when the kernel is
  created. A `config` closure passed to `bootKernel()` is applied *last* and therefore wins.
- `Internal\PimcoreConfigurator` — collects *and* applies in `@before`, resets in reverse order in
  `@after`. Only configurators whose `apply()` completed are reset. Boots a kernel at most once, lazily,
  when a configurator declares `requiresBootedKernel()`, and shuts it down in a `finally`.

**When writing a `ConfigurePimcore` attribute, keep the state backup in an instance property, never a
static one.** The same attribute may appear on the class and the method; each instance must restore what
it saw itself. This was a real bug — see `tests/Unit/Attribute/Pimcore/StackedAttributeTest.php`.

### Database reset lifecycle

- `ResetDatabase` (trait, `@mixin KernelTestCase`) — the entry point consumers use on a test class that
  needs a real database:
  - `#[BeforeClass] _resetDatabase()` — runs once per class (guarded by `DatabaseResetter::hasBeenReset()`,
    itself skippable via the `DISABLE_DATABASE_RESET` server var): boots a kernel, fully drops/recreates
    the database and installs Pimcore fresh (or from a dump).
  - `#[Before] _resetSchema()` — runs before *every* test method: drops and recreates just the schema,
    unless `dama/doctrine-test-bundle` is active (in which case each test is already isolated in a
    transaction, so schema reset is skipped).
- `Internal\Database\DatabaseResetter` — static coordinator; decides whether DAMA's `StaticDriver` is
  active (must temporarily disable "keep static connections" while resetting) and whether a `doctrine`
  service exists in the container at all before doing any work.
- `Internal\Database\PimcoreDatabaseResetter` — does the actual work via Symfony console commands run
  in-process (`RunCommand`): `doctrine:database:drop|create`, `doctrine:schema:drop|update`, and
  Pimcore's `pimcore:deployment:classes-rebuild`. Installation goes through `PimcoreDatabaseInstaller`
  on Pimcore 2026+ and `LegacyPimcoreInstaller` on 11/12.
  - If `DATABASE_DUMP_LOCATION` is set (non-empty), schema reset falls back to drop+recreate the whole
    database and import the dump via `SqlDumpImporter`, rather than a schema-only diff.
- `Internal\Database\DoctrineSchemaAssetFilter` — scopes `doctrine:schema:*` operations to only the
  tables managed by a given entity manager, so multi-connection/multi-manager setups don't clobber
  tables owned by another manager. Handles both ORM 2 array mappings and ORM 3 mapping objects.

### The deprecation layer

Everything that was public in v0.14 still exists under its old name and emits a deprecation via
`trigger_deprecation()` (hence `symfony/deprecation-contracts` in `require`; it is satisfied by the
`symfony/contracts` metapackage that Pimcore pulls in). Three patterns:

- `class_alias()` + a dead `if (false) { ... }` declaration, for classes/interfaces/attributes that must
  stay *the same* symbol (`Kernel\TestKernel`, `Pimcore\BootstrapPimcore`, `Test\Attribute\*`)
- a thin subclass or a trait that `use`s the new one (`Test\ConfigurableKernelTestCase`,
  `Test\ConfigurableWebTestcase`, `Database\ResetDatabase`)
- the original implementation kept in place, deprecated (`Pimcore\With*`)

These live under `src/{Kernel,Test,Database,Pimcore}` and are excluded from PHPStan. `UPGRADE-0.15.md`
maps old to new. When adding a shim, only cover symbols that were actually released.

### `tests/app`

A minimal Pimcore application used only to run this library's own test suite against a real kernel —
mirrors what a consumer project's `tests/app` would look like. `config/` holds config shared by all
Pimcore versions; `config/pimcore12/` holds version-specific overrides (see
`TestKernel::configureContainer` for the loading order). `tests/Fixtures/` contains throwaway
bundles/controllers/routes and the attribute fixtures used by the unit tests.

## Known defects

Two are open and documented in `UPGRADE-0.15.md`:

- `Attribute\Pimcore\Cache` does not work on Pimcore 11: it is the only attribute needing a booted
  kernel, and `Pimcore\Cache::isEnabled()` resolves its handler through the container, which is gone
  once `PimcoreConfigurator` has shut its own kernel down. Contaminates the whole test class.
- Combining `ConfigurableKernel` and `ConfigurablePimcore` on one test case fails under PHPUnit 9 — the
  two traits' `@before` hooks have no pinned relative order. PHPUnit 9 also misattributes the resulting
  failures to the wrong test names, so trust the assertion message over the reported method.

## Code style / static analysis notes

- PHPStan runs at level 8 against `src/` only, with `reportIgnoresWithoutComments: true` — any inline
  `@phpstan-ignore` needs a comment explaining why. Baseline lives in `phpstan-baseline.neon` and is
  shared by `phpstan.neon` (Pimcore 11/12) and `phpstan-2026.neon`; version-dependent entries carry
  `reportUnmatched: false`. Run **both** configurations when touching version-conditional code.
- `trait.unused` is ignored for the consumer-facing traits and everything in `src/Pimcore/*` and
  `src/Database/*`, since those are only ever used by consumer projects, not this repo itself.
- PHPUnit metadata is written **twice** — a doc-comment annotation *and* the attribute (`@test` +
  `#[Test]`, `@dataProvider` + `#[DataProvider]`, `@before` + `#[Before]`, …). PHPUnit 9 only reads the
  annotations, PHPUnit 10+ warns about them but still honours them. Do not drop either form.
- `composer.json` is kept normalized (`ergebnis/composer-normalize`) and dependency usage is checked with
  `shipmonk/composer-dependency-analyser` (config in `composer-dependency-analyser.php`, which branches
  on the installed Pimcore version at config-load time) — both run in the CI QA workflow.
- CI (`.github/workflows/tests.yaml`) runs the full Pimcore 11/12/2026 × PHP 8.1–8.5 × PHPUnit 9-11 ×
  highest/lowest-dependency matrix; if you change dependency constraints in `composer.json`, check
  whether the exclude/include matrix there still makes sense.
