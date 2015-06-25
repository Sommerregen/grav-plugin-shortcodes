# [Grav Shortcodes - A documentation](https://github.com/sommerregen/grav-plugin-shortcodes)

### Summary

> Embed is a shortcut to add a custom summary to a page.

##### Full syntax:

```twig
{{% summary render="html"|"twig"|"html+twig"|"raw" %}}
  CONTENT
{{% end %}}

OR

{{% summary "html"|"twig"|"html+twig"|"raw" %}}
  CONTENT
{{% end %}}
```

##### Options:

- [`render`]() *[optional]* -- (string)<br />
  Render content according to the specified format. This can be "html", "twig" or "html+twig" to render CONTENT as HTML, to render Twig syntax only or to render both. If `render` is empty or set to "raw", CONTENT will not be processed. The keyword can be omitted. (**default: "html"**)
