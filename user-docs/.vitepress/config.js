/**
 * Documentation site configuration for VitePress
 * This can be adapted for other static site generators
 */

export default {
  title: 'Isotone Documentation',
  description: 'Modern PHP CMS with WordPress-compatible hooks',
  base: '/docs/',
  
  themeConfig: {
    logo: '/logo.svg',
    
    nav: [
      { text: 'Guide', link: '/getting-started/installation' },
      { text: 'Developers', link: '/developers/themes' },
      { text: 'API', link: '/api/theme-api' },
      { text: 'GitHub', link: 'https://github.com/rizonesoft/isotone' }
    ],
    
    sidebar: {
      '/getting-started/': [
        {
          text: 'Getting Started',
          items: [
            { text: 'Installation', link: '/getting-started/installation' },
            { text: 'Configuration', link: '/getting-started/configuration' },
            { text: 'First Steps', link: '/getting-started/first-steps' }
          ]
        }
      ],
      '/developers/': [
        {
          text: 'Development',
          items: [
            { text: 'Theme Development', link: '/developers/themes' },
            { text: 'Plugin Development', link: '/developers/plugins' },
            { text: 'Hooks Reference', link: '/developers/hooks' },
            { text: 'Template Functions', link: '/developers/template-functions' }
          ]
        }
      ],
      '/api/': [
        {
          text: 'API Reference',
          items: [
            { text: 'Theme API', link: '/api/theme-api' },
            { text: 'Content API', link: '/api/content-api' },
            { text: 'Database Models', link: '/api/models' },
            { text: 'REST Endpoints', link: '/api/rest' }
          ]
        }
      ],
      '/guide/': [
        {
          text: 'User Guide',
          items: [
            { text: 'Admin Dashboard', link: '/guide/admin' },
            { text: 'Managing Content', link: '/guide/content' },
            { text: 'Themes & Appearance', link: '/guide/themes' },
            { text: 'Plugins', link: '/guide/plugins' }
          ]
        }
      ]
    }
  }
}