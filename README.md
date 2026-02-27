# Switch-Hub

This workspace now includes both:

- `switch-business-hub-ai-v2.0.0.zip` (plugin)
- `switch-graphics.zip` (installable WordPress theme)
- `switch-digital-card.zip` (installable WordPress plugin)

## Plugin usage (`switch-digital-card.zip`)

Install from **Plugins > Add New > Upload Plugin** and activate.

Use shortcode:

```text
[switch_digital_card]
```

Plugin settings:

- **Settings > Switch Digital Card**
- Configure:
  - slide image URLs
  - contact/social/menu/custom links
  - gradient colors and wave color
  - top fade colors/height
  - custom SVG shape paths (main + black overlay)
  - button/icon/font sizes
  - mobile fit + no-scroll behavior

The plugin intentionally does **not** render its own footer, so the active theme footer is used.

## Theme usage (`switch-graphics.zip`)

Install from **Appearance > Themes > Add New > Upload Theme**.

After activating:

1. Set your WordPress menu in **Appearance > Menus** (location: Primary Menu).
2. Open **Appearance > Customize**:
   - **Menu (burger)** section:
     - Menu title
     - Header gradient start/end (supports hex, rgb, rgba, or `transparent`)
     - Menu icon fill (gradient start/end)
     - Menu icon outline color
     - Menu icon outline thickness
   - **Footer Content** section:
     - Footer year
     - Footer company name
     - Footer company link
     - Footer link text color

The theme menu opens as a centered popup modal with an `X` close button.
Footer output is:
`<year> © Designed & Powered By: <company link>`
