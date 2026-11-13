#!/bin/sh
set -eu
repo_dir=$(CDPATH= cd -- "$(dirname "$0")/.." && pwd)
cd "$repo_dir"
mkdir -p dist
package_tmp=$(mktemp -d "$repo_dir/dist/package.XXXXXX")
trap 'rm -rf "$package_tmp"' EXIT HUP INT TERM
zip -qr "$package_tmp/commonplace-theme.zip" commonplace-theme -x '*/.DS_Store' '*/._*' '*.map' '*/editor-dev.css'
mv "$package_tmp/commonplace-theme.zip" "$repo_dir/dist/commonplace-theme.zip"
echo "Created $repo_dir/dist/commonplace-theme.zip"
