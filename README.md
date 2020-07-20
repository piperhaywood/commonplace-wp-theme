# Commonplace WordPress theme

This is a simple, translation-ready WordPress theme that came about after conversations with designer [Bec Worth](http://www.youwouldlovethis.com/) about the usefulness of a Commonplace Book and what it could feel like on the web. Technically, it borrows heavily from my Notebook theme. Most text is set in the variable versions of [Source Serif Pro](https://github.com/adobe-fonts/source-serif-pro) and [Source Sans Pro](https://github.com/adobe-fonts/source-sans-pro) with appropriate fallbacks for Chinese and Japanese characters.

This is a WIP! 🚧 Currently in use on [piperhaywood.com](https://piperhaywood.com). Please feel free to raise an issue if you notice anything weird.

## Installation

The compiled theme is in `/commonplace-theme`. This directory can be zipped and uploaded in the WordPress theme management page, or the directory can be manually uploaded via cPanel or FTP. For further instructions, see the WordPress documentation page [Using Themes](https://wordpress.org/support/article/using-themes/#adding-new-themes-using-the-administration-screens).

## Shortcodes

The shortcode `[notebooksearch]` displays a search form.

The shortcode `[notebookindex]` displays an alphabetical index of terms. The Index shortcode can be passed attributes that modify the included taxonomies, the post count threshold, and whether or not year archive links are displayed. For example, `[notebookindex taxonomy="category" years="false" count="2"]` will pull through only categories with a post count of at least 2 and no year archive links. If no attributes are passed to the `notebookindex` shortcode, then these default attributes will be used: `[notebookindex taxonomy="post_tag, category, post_format" years="true" count="1"]`.

The shortcode `[notebooklist]` displays a chronological list of posts.

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

This theme is designed to work well with the [Related Posts By Taxonomy](https://en-gb.wordpress.org/plugins/related-posts-by-taxonomy/). If activated, related posts will be displayed using the same styles as the `[notebooklist]` shortcode.

This theme uses [Prism](https://prismjs.com/) for syntax highlighting.

## Development

To set this repo up, install WordPress in a separate directory using this [multi-environment `wp-config.php` gist](https://gist.github.com/piperhaywood/2a7217964335e22574784153eab1d38b) if useful. Note that if you’re using Sequel Pro to manage your local databases, you probably need to use the user `root`, password `root`, and hostname `127.0.0.1`.

Once you have WordPress set up, symlink the theme folder `/commonplace-theme` within this repo in to your WP site’s `/wp-content/themes` directory. You can do this with multiple WordPress installations, which can be useful for testing styles against different content. I use this with one WordPress installation that reflects [piperhaywood.com](https://piperhaywood.com) and another that uses WordPress’s theme testing database.

Once you have the WordPress installation set up and the theme symlinked, run `npm i` from the root of this repo to install dependencies. Run `gulp` to build the theme or `gulp dev` for development. The command `gulp dev` will compile the files and then use BrowserSync for live reloading. To set a BrowserSync proxy other than the default `localhost:8888`, run `gulp dev --proxy custom-proxy` (replace `custom-proxy`).

## Technical notes

The build borrows significantly from my Notebook theme. I’ve opted to be pretty opinionated with the base styles, and then individual components like the menu in the header or the archive list are given classes and styled separately. The classes are a little wild currently, an amalgamation of a few different styles. I’m hoping to streamline them soon. WordPress-specific styles are confined to the `wp` namespaced CSS files. These styles cover the craziness that Gutenberg and the classic editor can spit out. I’m 100% sure I haven’t caught every eventuality that can arise, so that’s an ongoing task.
