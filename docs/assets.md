# [Grav Shortcodes - A documentation](https://github.com/sommerregen/grav-plugin-shortcodes)

### Assets

> Assets is a shortcut to add CSS and JS assets directly to the site.

##### Full syntax:

```twig
{{% assets type="css"|"js" inline=false|true priority=10 load="async"|"defer" pipeline=false %}}
  CONTENT
{{% end %}}
```

##### Options:

- [`type`]() **[required]** -- (string)<br />
  The type of the asset. Currently supported values are "css" or "js".

- [`inline`]() *[optional]* -- (bool)<br />
  Determines whether assets should be an outputted as inline or as a block element. Defaults to `inline=false`, which means that every line of the contents will be interpreted as an URL for a CSS/JS script. The URL can be a *relative*, *absolute*, *agnostic*, an *external* link or can be a stream pointing to an URL in Grav such as `theme://js/myspecial.js`. This can be useful to reference custom JS to a page without the need of an absolute URL.

- [`priority`]() *[optional]* -- (int)<br />
  Sets the priority of the assets. Higher numbers mean a high priority and assets are set further top (**default: 10**).

- [`load`]() *[optional]* -- (bool)<br />
  Sets whether assets should be loaded asynchronously ("async") or be deferred ("defer"). This option only plays a role for JS assets.

- [`pipeline`]() *[optional]* -- (bool)<br />
  Sets whether your assets should be pipelined or not (**default: false**). This option is only available for `inline=false`.
