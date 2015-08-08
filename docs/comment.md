# [Grav Shortcodes - A documentation](https://github.com/sommerregen/grav-plugin-shortcodes)

### Comment

> The `comment` shortcode allows you to use comments and annotations in a Markdown document without being outputted to the user.

##### Full syntax:

```twig
{{% comment %}}
  CONTENT
{{% end %}}
```

##### Note:

Shortcodes already provides a comment mechanism using the `{# ... #}` tag as already known from Twig and is an alias for this shortcode.

Comments in Markdown are also available in the core Markdown specification (http://daringfireball.net/projects/markdown/syntax#link). That is

```markdown
[comment]: <> (This is a comment, it will not be included)
[comment]: <> (in  the output file unless you use it in)
[comment]: <> (a reference style link.)
```

Or going further:

```
[//]: <> (This is also a comment.)
```

To improve platform compatibility (and to save one keystroke) it is also possible to use # (which is a legitimate hyperlink target) instead of <>:

```
[//]: # (This may be the most platform independent comment)
```

It may also be prudent to insert a blank line before and after this type of comments, because some Markdown parsers may not like link definitions brushing up against regular text.

This should work with most Markdown parsers, since it's part of the core specification. (even if the behavior when multiple links are defined, or when a link is defined but never used, is not strictly specified).

Source: http://stackoverflow.com/questions/4823468/store-comments-in-markdown-syntax

Jekyll or Octopress are automatically supported by the Shortcodes plugin. However to achieve compatibility with them it is recommended to write comments always as

```
{% comment %}
    These commments will not include inside the source.
{% endcomment %}
```
