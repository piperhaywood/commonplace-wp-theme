# Commonplace WordPress theme

This is a translation-ready WordPress theme that came about through conversations with designer [Bec Worth](http://www.youwouldlovethis.com/) about the usefulness of a Commonplace Book and what it could feel like on the web. Technically, it borrows heavily from my Notebook theme. Most text is set in the variable versions of [Source Serif Pro](https://github.com/adobe-fonts/source-serif-pro) and [Source Sans Pro](https://github.com/adobe-fonts/source-sans-pro) with appropriate fallbacks for Chinese and Japanese characters.

> ⚠️ **Fair warning:** This is a work in progress! 🚧 It’s currently in use on [piperhaywood.com](https://piperhaywood.com), but there are definitely bugs, particularly when it comes to Gutenberg compatibility. Please feel free to [raise an issue](https://github.com/piperhaywood/commonplace-wp-theme/issues) if you notice anything weird or have suggestions. Unfortunately I do not have the capacity to provide individualized support, so **it is provided as-is** for you to work with as you wish.

## Installation and updates

I deliberately do not list this theme in the WordPress theme library because I do not have capacity to go through and maintain that process. As such, installation and all updates must be done manually as described below.

To install the theme, compress the compiled theme folder `/commonplace-theme` and upload that ZIP file in the WordPress theme management page. Alternatively, the ZIP file can be manually uploaded via your hosting dashboard such as cPanel or FTP/SFTP. For further instructions, see the WordPress documentation page [Using Themes](https://wordpress.org/support/article/using-themes/#adding-new-themes-using-the-administration-screens).

## Shortcodes

The shortcode `[notebooksearch]` displays a search form.

The shortcode `[notebookindex]` displays an alphabetical index of terms. The Index shortcode can be passed attributes that modify the included taxonomies, the post count threshold, and whether or not year archive links are displayed. For example, `[notebookindex taxonomy="category" years="false" count="2"]` will pull through only categories with a post count of at least 2 and no year archive links. If no attributes are passed to the `notebookindex` shortcode, then these default attributes will be used: `[notebookindex taxonomy="post_tag, category, post_format" years="true" count="1"]`.

The shortcode `[notebooklist]` displays a chronological list of posts in batches
of 50. “Load more posts” appends the next batch; without JavaScript, a “Next posts”
link opens the next batch. Multiple lists on the same page paginate independently.
Loaded batches and scroll position are saved for the current browser tab’s history
entry when session storage is available. This disposable cache retains at most
eight list/grid snapshots, uses approximately 2 MiB of text storage, and expires
snapshots after 30 minutes; browser back/forward caching remains independent.

The posts index, archives, and search results also offer opt-in layout previews:
add `?cp_view=grid` or `?cp_view=list` to the URL (or `&cp_view=…` when it already
has a query string). The grid loads 24 posts per batch and the list loads 50, with
ordinary archive pagination as the fallback. The view switcher is still in progress.

List images (including related posts) use the theme's `cp-list-thumb` image size,
which fits within 240 × 240 pixels without cropping. When installing this theme
on an existing WordPress site, or updating from a version without this image size,
regenerate thumbnails for existing uploads:

`wp media regenerate --image_size=cp-list-thumb --only-missing --yes`

Alternatively, use a thumbnail regeneration plugin. Until regeneration is
complete, list images fall back to the existing `thumbnail` size. New uploads get
the list size automatically.

## Utility classes

See the `/css/_utility.scss` styles for utility classes. The classes that can be useful for content editing are described below.

`.hidden` sets the element to `display: none;`. Do NOT use that class if it is an important UI element like a form label; instead, use the `.visuallyhidden` class for accessibility.

`.screenshot` will give an element a bit of a box shadow, similar to what a window looks like on a Mac. It can be useful for displaying screenshots.

`.pixels` should be used on pixel art images to render with crisp edges.

`.thumbs` should be used on wrapper elements that contain a series of thumb-like images. If you add the class `.thumbs--with-border` to this wrapper, the images will be given a solid grey border.

`.smallcaps` can be used to set text in small caps.

`.mocking` can be used for sarcastic or mocking text, the sort of thing might NoRMaLy wRItE LIkE tHiS if you didn’t have this class.

`.discreet-links` can be used to make links discreet (only underline on hover).

`.notes` and `.small` can be used for small text.

## Plugins & Extras

Related posts are implemented in `commonplace-theme/func/related-posts.php`.
On individual posts, “See also” shows up to five published posts ranked by shared
tags, then newest publication date (with post ID as a final tie breaker). The
current post is excluded; posts without matching tags have no related list.
The existing theme list renderer supplies the markup. Source terms reuse
WordPress relationship caches, and ranking aggregates matching relationships once
before selecting the requested posts.

The legacy `[related_posts_by_tax]` shortcode remains available. Supported
attributes are `post_id`, `taxonomies` (comma-separated public taxonomy names;
defaults to all public taxonomies), `posts_per_page` (default 5; -1 for all),
`title`, `before_shortcode`, `after_shortcode`, `before_title`, and `after_title`.
Results are always published posts, including when viewed by an administrator.
Other plugin attributes, hooks, widgets, and display formats are no longer
provided. The local content audit found no saved uses of the shortcode or widget;
sites using those plugin features should review them before updating.

The read-only efficiency check compares rankings against the previous correlated
query for every published post and verifies cached source-term reuse:
`wp eval-file /absolute/path/to/commonplace-wp-theme/tests/related-posts-efficiency.php`.

The former bundled Related Posts By Taxonomy library has been removed. No
related-posts REST endpoint, debug output, or lazy-loading endpoint is registered.
Run the route regression check with
`wp eval-file /absolute/path/to/commonplace-wp-theme/tests/related-posts-rest.php`
and the query checks with
`wp eval-file /absolute/path/to/commonplace-wp-theme/tests/related-posts.php`
against a local WordPress installation with this theme active. The query checks
create temporary fixtures and delete them afterward; use a development database.

This theme uses [Prism](https://prismjs.com/) for syntax highlighting.

## Build and development

The installable theme lives in `commonplace-theme/`. Compiled assets are included in this repository, so Node is only needed if you want to change and rebuild the theme’s styles or JavaScript.

Use the Node version specified in `.nvmrc`. With nvm installed, run these commands from the repository root:

```sh
nvm install
nvm use
npm ci
npm run build
```

The build writes frontend CSS, editor CSS, and JavaScript to `commonplace-theme/dist/`. Rebuild before packaging the `commonplace-theme/` directory for installation. The build also generates the theme metadata header in `commonplace-theme/style.css` from `package.json`; edit the package metadata rather than that generated file.

### Package for installation

From the theme repository root, run:

```sh
nvm use
npm ci
npm run package
```

`npm run package` builds production assets and writes `dist/commonplace-theme.zip`,
with `commonplace-theme/` at the archive root. It excludes source maps, macOS
metadata, and the editor development stylesheet. The ZIP packages the current
working tree, including uncommitted theme edits; review Git status and test the
production build before uploading it.

The root `dist/` directory is ignored by Git; compiled assets in
`commonplace-theme/dist/` remain tracked. Attach the ZIP to a GitHub Release tied
to a version tag when publishing a release. Packaging does not bump the version
or deploy the theme.

To increment the patch version for a release and regenerate the theme header:

```sh
npm version patch --no-git-tag-version
npm run build
```

The version command updates `package.json` and `package-lock.json` together without creating a Git commit or tag. Commit those changes with the rebuilt theme files.

### My development setup

I use Local by Flywheel for development specific to piperhaywood.com. Note to self: see the README in the private `wordpress-personal-site` repository which includes a script that supplies the development URLs and HTTPS settings automatically.

### Developing locally

If you’re not me and you’re working with this theme, you’ll need a separate local WordPress installation outside of this repository using whatever WordPress development environment setup you prefer. Link this repository’s `commonplace-theme/` directory into your installation’s `wp-content/themes/` directory and activate the theme.

To enable live updates while working with this repository:

1. Copy `.env.example` to `.env.local` and set your WordPress origin and Vite server URL.
2. Add the following to your local `wp-config.php`, before WordPress loads:

   ```php
   define('WP_ENVIRONMENT_TYPE', 'local');
   define('CP_VITE_DEV_SERVER', 'http://localhost:5173');
   ```

3. Make sure `CP_VITE_DEV_SERVER` matches `VITE_DEV_SERVER` in `.env.local`. If WordPress uses HTTPS, Vite should also use HTTPS; supply trusted certificate and key paths using the settings in `.env.example`.
4. Run `npm run dev` and browse your local WordPress URL.

Keep WordPress and Vite running while developing. WordPress serves the pages; Vite serves the development styles and JavaScript. Frontend CSS updates automatically, and PHP changes reload the page. Save your work and refresh the WordPress editor to see editor stylesheet changes.

To check the production build locally, stop Vite, remove or comment out `CP_VITE_DEV_SERVER`, and run `npm run build`. Check the frontend and editor before packaging. Restore the setting when returning to development.

### Source files

- Frontend styles: `css/main.scss`
- Editor styles: `css/editor.scss`
- JavaScript entry point: `js/main.js`
- Shared font declarations: `commonplace-theme/fonts/fonts.css`

Edit the source files rather than generated files in `dist/`. Fonts are served directly by WordPress and are not copied by the build.

`npm run lint:css` checks the Sass files. Styles use Sass modules (`@use`) rather than deprecated Sass imports. The editor uses `meta.load-css()` to keep shared styles in their original cascade order.

Build and lint tooling require Node 22.22.2+, 24.15.0+, or 26+ (see `package.json` for the supported ranges). The version in `.nvmrc` satisfies these requirements. Run `npm run lint:css` and `npm run build` after dependency updates, and check both the frontend and WordPress editor before releasing.

Prism is a development dependency bundled into `js/prism.js`. After updating
`prismjs`, run `npm run build:prism` and then `npm run build`. The generator in
`scripts/build-prism.cjs` preserves the selected languages and plugins; the
custom theme styles remain in `css/_prism.scss`.

### Notes

The CSS classes are a little wild currently, an amalgamation of a few different styles. I’m hoping to streamline them soon. WordPress-specific styles are confined to the `wp` namespaced CSS files. These styles cover the craziness that Gutenberg and the classic editor can spit out. I’m 100% sure I haven’t caught every eventuality, so that’s an ongoing task.

## Continuous integration

The **Theme CI** workflow runs on pull requests and pushes to `main`, and can also be triggered manually.
It uses the Node version in `.nvmrc` to install locked dependencies with `npm ci`,
lint Sass, build production assets, and check that the committed
`commonplace-theme/dist/` files and generated `commonplace-theme/style.css` match
a fresh build. Run `npm run build` and commit the generated files whenever the
source assets or theme metadata change.

The editor stylesheet uses `meta.load-css()` after local rules to preserve the
cascade without deprecated Sass imports or a Stylelint exception.

A separate job checks the syntax of every PHP file in `commonplace-theme/`,
using PHP 8.2. This is a syntax check;
it does not boot WordPress or verify runtime compatibility. WordPress Coding
Standards run in a separate job as described below. Browser tests are not yet part
of this workflow.

### Query regression checks

With the theme active in a local WordPress installation, run
`wp eval-file /absolute/path/to/commonplace-wp-theme/tests/query-efficiency.php`.
This read-only check compares aggregate year counts and post-term membership with
the previous queries, then checks alphabetical ordering, post-format placement,
empty/error results, and cached lookups without creating database records.
Ordering fixtures require at least one published post.

Post terms are sorted locally by accent-normalized, case-insensitive name, with
term ID breaking ties. Post formats use their stored slugs for sorting and retain
their translated labels for display. This avoids loading all site terms merely to
sort one post's terms; ordering can differ from the database's locale collation.

### PHP coding standards

PHP_CodeSniffer checks the theme's own PHP against `WordPress-Extra`, which
includes WordPress core conventions and additional best-practice checks. The
project overrides the indentation rules to use **two spaces instead of tabs**,
including in multiline arrays and switch statements. Use the Composer commands
below: they pass `--tab-width=2` to override the imported WordPress default. Other
WordPress checks, including security and escaping checks, remain enabled. The
check does not require a running WordPress installation. Install Composer and PHP 8.2 with
`mbstring` and XML extensions, then run from the repository root:

```sh
composer install
composer lint:php
```

To apply automatic formatting fixes, run `composer format:php`, review the diff,
and rerun `composer lint:php`. Some findings, including output escaping, require
manual changes. Commit `composer.lock` so local checks and CI use the same tools;
`vendor/` is development-only and is not included in the installable theme.

The ruleset lives in `.phpcs.xml.dist` and covers all theme PHP, including related
posts. Documented inline exceptions cover intentional global-post setup,
WordPress or theme-rendered HTML, Vite's development scripts, and retained
Gutenberg examples. The separate PHP syntax job checks all theme PHP as well.

### Post loading regression checks

Run `node --test tests/post-history.test.mjs tests/post-request.test.mjs` to check history-cache size/count
limits, expiration, storage failures, position-only updates, and request deadlines
without WordPress. Post requests time out after 15 seconds and allow retry through
the existing load-more button.
