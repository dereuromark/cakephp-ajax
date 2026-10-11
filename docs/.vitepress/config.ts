import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'cakephp-ajax',
  description: 'JSON responses, HTML fragments, redirects, and flash messages for CakePHP Ajax requests.',
  base: '/cakephp-ajax/',
  cleanUrls: true,
  lastUpdated: true,
  sitemap: { hostname: 'https://dereuromark.github.io/cakephp-ajax/' },
  themeConfig: {
    nav: [
      { text: 'Installation', link: '/guide/installation' },
      { text: 'Getting started', link: '/guide/getting-started' },
      { text: 'Reference', link: '/reference/component' },
    ],
    sidebar: [
      {
        text: 'Guide',
        items: [
          { text: 'Overview', link: '/guide/' },
          { text: 'Installation', link: '/guide/installation' },
          { text: 'Getting started', link: '/guide/getting-started' },
        ],
      },
      {
        text: 'Reference',
        items: [
          { text: 'Ajax Component', link: '/reference/component' },
          { text: 'Ajax View', link: '/reference/view' },
          { text: 'Contributing', link: '/guide/contributing' },
        ],
      },
    ],
    search: { provider: 'local' },
    socialLinks: [{ icon: 'github', link: 'https://github.com/dereuromark/cakephp-ajax' }],
    editLink: {
      pattern: 'https://github.com/dereuromark/cakephp-ajax/edit/master/docs/:path',
      text: 'Edit this page on GitHub',
    },
    footer: { message: 'Released under the MIT License.' },
  },
})
