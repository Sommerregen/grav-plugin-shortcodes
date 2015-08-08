# [Grav Shortcodes - A documentation](https://github.com/sommerregen/grav-plugin-shortcodes)

### Twig

> Twig is a shortcut to render custom texts using the Twig templating engine.

##### Full syntax:

```twig
{{% twig template="name" %}}
  CONTENT
{{% end %}}
```

##### Options:

- [`template`]() *[optional]* -- (string)<br />
  Render custom text using a predefined template. The content of the shortcode is passed to the template as a clone of the current page.
