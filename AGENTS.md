# Agent guidance

## Project and development

- Read `README.md` for setup, local preview, and release instructions.
- `commonplace-theme/` is the installable WordPress theme. WordPress runs in a separate installation; Vite serves development assets, not the site itself.
- Use the Node version in `.nvmrc` and install locked dependencies with `npm ci`. Use `composer install` for PHP development checks.
- Keep machine-specific URLs, certificate paths, and other local configuration in ignored `.env.local`. For Piper's Local setup, consult the private `wordpress-personal-site` README when available.

## Editing conventions

- Edit Sass in `css/` and JavaScript in `js/`. Entry points are `css/main.scss`, `css/editor.scss`, and `js/main.js`; shared font declarations live in `commonplace-theme/fonts/fonts.css`.
- Rebuild generated files in `commonplace-theme/dist/` rather than editing them directly. Commit rebuilt assets with their source changes.
- The build generates the metadata header in `commonplace-theme/style.css` from `package.json`. Edit package metadata and rebuild instead of editing that header directly.
- Use Sass modules (`@use`). Preserve stylesheet cascade order, including the editor's use of `meta.load-css()` after local rules.
- Follow `.phpcs.xml.dist`: WordPress-Extra with two-space indentation instead of tabs. Use the Composer scripts, which supply the required tab width.
- Preserve WordPress escaping, sanitization, translation, and accessibility conventions. Keep existing public shortcodes and theme behavior compatible unless the task calls for a change.
- The bundled `commonplace-theme/func/related-posts-by-taxonomy/` library is excluded from coding standards checks. Avoid incidental reformatting there; PHP syntax checks still cover it. The custom template in `commonplace-theme/related-post-plugin/` is checked.

## Verification

- For Sass changes, run `npm run lint:css`. For asset, build-tooling, or theme-metadata changes, run `npm run build` and include any generated changes.
- For theme PHP changes, run `composer lint:php` and check changed PHP files with `php -l`. If using `composer format:php`, review its diff and rerun the lint check.
- Before a PR, run the applicable Theme CI checks described in `README.md`. CI checks Sass, production asset freshness, all theme PHP syntax on PHP 8.2, and WordPress coding standards. It does not verify WordPress runtime behavior or browser rendering.
- For visual or runtime changes, check affected frontend views and the WordPress editor where relevant. Verify production assets with the Vite development override disabled; restore local development settings afterward.
- Report what was verified and any checks that could not run. Documentation-only changes need a diff review, not an asset rebuild.

## Branches and pull requests

- Start new work on a `codex/` branch from the latest `main`, unless the user specifies another base or branch.
- Before opening a PR, fetch `origin` and merge the latest `origin/main` into the working branch. Resolve conflicts, then rerun checks affected by the merge. Use a merge rather than rebasing unless the user requests otherwise.
- Keep changes focused and preserve unrelated work. Review the final diff for generated assets, accidental local configuration, and unrelated formatting.
- Describe the concrete change and relevant verification in the PR. Do not bump the theme version automatically for routine work; follow the README's versioning procedure when preparing a requested release.
