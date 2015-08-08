# [Grav Shortcodes - A documentation](https://github.com/sommerregen/grav-plugin-shortcodes)

### Embed

> Embed is a shortcut to embed pages or the page content into other pages using simple markdown syntax.

##### Full syntax:

```twig
{{% embed page="/path-or-slug" modular=false|true template="name" %}}
// OR
{{% embed "/path-or-slug" modular=false|true template="name" %}}
```

##### Options:

- [`page`]() **[required]** -- (string)<br />
  The path to a (modular) page. The path equals the slug of the page with a perpended slash. The keyword is optional.

- [`modular`]() *[optional]* -- (bool)<br />
  Determines whether the page or the page content should be embedded. Usually this option is automatically set to the correct value and need not to be altered, otherwise correct the auto-detection by specifying a value.

- [`template`]() *[optional]* -- (string)<br />
  The template used for rendering a page. This option is only available for `modular=true` (**default: ""**).
