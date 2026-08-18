# Upgrading to 0.15

0.15 moves the public API into a flatter, more consistent shape and replaces the behavior-switching
traits with attributes.

**Nothing breaks immediately.** Every symbol that existed in 0.14 is still there, forwarding to its new
home and emitting a deprecation. Work through the tables below at your own pace; the old names will be
removed in 1.0.

To see what your test suite still uses, run it with deprecations displayed:

```shell
vendor/bin/phpunit --display-deprecations
```

## Requirements

| | 0.14 | 0.15 |
|---|---|---|
| PHP | 8.1 – 8.5 | 8.1 – 8.5 |
| Pimcore | 11.5, 12.0, 2026.1 | 11.5, 12.0, 2026.1 |
| PHPUnit | 9.6, 10.5, 11.5 | 9.6, 10.5, 11.5 |

No requirement changed — 0.15 is a pure API change.

## Renamed and moved

The rule behind the new layout: **the root namespace holds what you write in your test case (the traits)
plus the two entry points; sub-namespaces hold supporting machinery.**

### Entry points

| 0.14 | 0.15 |
|---|---|
| `Neusta\Pimcore\TestingFramework\Kernel\TestKernel` | `Neusta\Pimcore\TestingFramework\TestKernel` |
| `Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore` | `Neusta\Pimcore\TestingFramework\BootstrapPimcore` |

### Test case base classes → traits

`ConfigurableKernelTestCase` and `ConfigurableWebTestcase` forced a base class on you. The same behavior
is now a trait you add to whichever `KernelTestCase` or `WebTestCase` you already extend.

| 0.14 | 0.15 |
|---|---|
| `extends Test\ConfigurableKernelTestCase` | `extends Pimcore\Test\KernelTestCase` + `use ConfigurableKernel` |
| `extends Test\ConfigurableWebTestcase` | `extends Pimcore\Test\WebTestCase` + `use ConfigurableKernel` |

```diff
-use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
+use Neusta\Pimcore\TestingFramework\ConfigurableKernel;
+use Pimcore\Test\KernelTestCase;

-class SomeTest extends ConfigurableKernelTestCase
+class SomeTest extends KernelTestCase
 {
+    use ConfigurableKernel;
```

### Database

| 0.14 | 0.15 |
|---|---|
| `Neusta\Pimcore\TestingFramework\Database\ResetDatabase` | `Neusta\Pimcore\TestingFramework\ResetDatabase` |

The rest of the `Database\` namespace was `@internal` and moved to `Internal\Database\`. If you were
using any of it directly, you were relying on internals — the classes are still there, under the new
namespace, and still `@internal`.

### Kernel configuration attributes

| 0.14 | 0.15 |
|---|---|
| `Test\Attribute\ConfigureContainer` | `Attribute\Kernel\ConfigureContainer` |
| `Test\Attribute\ConfigureExtension` | `Attribute\Kernel\ConfigureExtension` |
| `Test\Attribute\ConfigureRoute` | `Attribute\Kernel\ConfigureRoute` |
| `Test\Attribute\RegisterBundle` | `Attribute\Kernel\RegisterBundle` |
| `Test\Attribute\RegisterCompilerPass` | `Attribute\Kernel\RegisterCompilerPass` |
| `Test\Attribute\KernelConfiguration` | `KernelConfiguration` |

`KernelConfiguration` — the interface you implement for custom attributes — keeps its name and moves to
the root namespace. It was named after what it *is*, unlike the attributes, which are named after what
they *do*.

## Traits replaced by attributes

The `With*` traits applied to a whole test case class and could not be varied per test. Attributes can,
and they cover more of Pimcore's global state.

| 0.14 trait | 0.15 attribute |
|---|---|
| `Pimcore\WithAdminMode` | `#[Attribute\Pimcore\AdminMode]` |
| `Pimcore\WithoutCache` | `#[Attribute\Pimcore\Cache(false)]` |
| `Pimcore\WithInheritedValues` | `#[Attribute\Pimcore\DataObjectInheritance]` |
| `Pimcore\WithoutInheritedValues` | `#[Attribute\Pimcore\DataObjectInheritance(false)]` |
| — | `#[Attribute\Pimcore\RuntimeCache]` (new) |
| — | `#[Attribute\Pimcore\Versioning]` (new) |

Attributes need the `ConfigurablePimcore` trait on the test case:

```diff
-use Neusta\Pimcore\TestingFramework\Pimcore\WithAdminMode;
+use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\AdminMode;
+use Neusta\Pimcore\TestingFramework\ConfigurablePimcore;

+#[AdminMode]
 class SomeTest extends TestCase
 {
-    use WithAdminMode;
+    use ConfigurablePimcore;
```

Every attribute takes a single `bool $enable` that defaults to `true`, works on class *and* method level,
and restores the previous value afterwards. See the README for the full list.

## Known limitations in 0.15

Two defects are known and not yet fixed. Both predate this release:

- **`#[Cache]` does not work on Pimcore 11.** It is the only attribute that needs a booted kernel;
  on Pimcore 11 `Pimcore\Cache::isEnabled()` resolves its handler through the container, which is gone
  once the attribute's own kernel has been shut down. Use the deprecated `WithoutCache` trait there for
  now, or run the affected tests on Pimcore 12 or newer.
- **Combining `ConfigurableKernel` and `ConfigurablePimcore` on one test case does not work under
  PHPUnit 9.** The two traits contribute `@before` hooks whose relative order is not pinned; PHPUnit 10
  and newer resolve it correctly. Split the test case, or use PHPUnit 10+.
