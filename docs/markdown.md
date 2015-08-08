# [Grav Shortcodes - A documentation](https://github.com/sommerregen/grav-plugin-shortcodes)

### Markdown

> Markdown is a shortcut to parse texts using Markdown syntax in a document.

##### Full syntax:

```twig
{{% markdown extra=true|false auto_line_breaks=true|false auto_url_links=true|false escape_markup=true|false special_chars={'>':'gt', ...} %}}
```

##### Options:

- [`extra`]() *[optional]* -- (bool)<br />
  Enable support for Markdown Extra (**default: true**).

- [`auto_line_breaks`]() *[optional]* -- (bool)<br />
  Enable automatic line breaks (**default: false**).

- [`auto_url_links`]() *[optional]* -- (bool)<br />
  Enable automatic HTML links (**default: false**).

- [`escape_markup`]() *[optional]* -- (bool)<br />
  Escape markup tags into entities (**default: false**).

- [`special_chars`]() *[optional]* -- (array)<br />
  List of special characters to automatically convert to entities (**default: {'>': 'gt', '<': 'lt'}**).
