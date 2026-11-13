const { readFileSync, writeFileSync } = require('node:fs');
const { dirname, join } = require('node:path');
const components = require('prismjs/components.json');
const getLoader = require('prismjs/dependencies');
const { version } = require('prismjs/package.json');

const root = dirname(require.resolve('prismjs/package.json'));
const languages = 'markup css clike javascript css-extras graphql latex lilypond markup-templating php python jsx tsx regex ruby rust sass scss scheme sql twig typescript'.split(' ');
const plugins = 'line-highlight line-numbers highlight-keywords inline-color keep-markup command-line normalize-whitespace treeview'.split(' ');
const url = `https://prismjs.com/download.html#themes=prism&languages=${languages.join('+')}&plugins=${plugins.join('+')}`;
const chunks = [`/* PrismJS ${version}\n${url} */`];

// Prism resolves required grammars and orders components that modify others.
getLoader(components, ['core', ...languages, ...plugins]).load((id) => {
  const category = Object.keys(components).find((name) => id in components[name]);
  const template = components[category].meta.path;
  const path = template.replaceAll('{id}', id).replace(/\.js$/, '') + '.min.js';
  chunks.push(readFileSync(join(root, path), 'utf8'));
});

writeFileSync(join(__dirname, '../js/prism.js'), chunks.join('\n') + '\n');
