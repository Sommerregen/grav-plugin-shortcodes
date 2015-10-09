# v1.2.0
## 10/09/2015

1. [](#new)
  * Added support for Shortcode filter methods
2. [](#improved)
  * Assets from shortcode `{{% assets %}}` are now being cached with page contents
  * Added blueprints for Grav Admin plugin
  * Use `fieldset` fields to group shortcode options **(requires Grav 0.9.44+)**
3. [](#bugfix)
  * Fixed [#2](https://github.com/Sommerregen/grav-plugin-shortcodes/issues/2) (Not working with Grav's Admin Panel)

# v1.1.0
## 08/08/2015

1. [](#new)
  * Added admin configurations **(requires Grav 0.9.34+)**
  * Added `{{% comment %}}`, `{{% twig %}}`, `{{% markdown %}}` shortcodes
  * Added documentation about `{{% raw %}}` shortcode
  * Changed Shortcodes event to `onShortcodesInitialized`!
2. [](#improved)
  * Extended Twig Shortcodes capabilities for developers
  * Updated `README.md`

# v1.0.1
## 06/25/2015

1. [](#new)
  * Added [`twig` shortcode](docs/twig.md)
2. [](#improved)
  * Improved error handling in Twig
3. [](#bugfix)
  * Fixed indentation of source code
  * Fixed [#1](https://github.com/Sommerregen/grav-plugin-shortcodes/issues/1) (Error while trying to call a shortcodes directory instead of Shortcodes)

# v1.0.0
## 06/23/2015

1. [](#new)
  * ChangeLog started...
