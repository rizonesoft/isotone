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
      { text: 'Development', link: '/development/architecture' },
      { text: 'API', link: '/api-reference/endpoints' },
      { text: 'Toni AI', link: '/toni/overview' },
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
      '/development/': [
        {
          text: 'Development',
          items: [
            { text: 'Architecture', link: '/development/architecture' },
            { text: 'Project Structure', link: '/development/project-structure' },
            { text: 'Commands', link: '/development/commands' },
            { text: 'Routes', link: '/development/routes' },
            { text: 'Hooks System', link: '/development/hooks-system' },
            { text: 'Theme Customizer', link: '/development/customizer' },
            { text: 'Themes', link: '/development/themes' }
          ]
        }
      ],
      '/api-reference/': [
        {
          text: 'API Reference',
          items: [
            { text: 'Endpoints', link: '/api-reference/endpoints' },
            { text: 'Models', link: '/api-reference/models' },
            { text: 'Theme API', link: '/api-reference/theme-api' },
            { text: 'Content API', link: '/api-reference/content-api' }
          ]
        }
      ],
      '/automation/': [
        {
          text: 'Automation',
          items: [
            { text: 'Rules', link: '/automation/rules' },
            { text: 'Workflows', link: '/automation/workflows' },
            { text: 'CLI Tools', link: '/automation/cli-tools' }
          ]
        }
      ],
      '/toni/': [
        {
          text: 'Toni AI Assistant',
          items: [
            { text: 'Overview', link: '/toni/overview' },
            { text: 'Query Guidelines', link: '/toni/query-guidelines' },
            { text: 'Knowledge Base', link: '/toni/knowledge-base' }
          ]
        }
      ],
      '/troubleshooting/': [
        {
          text: 'Troubleshooting',
          items: [
            { text: 'Common Issues', link: '/troubleshooting/common-issues' },
            { text: 'Database Issues', link: '/troubleshooting/database' },
            { text: 'Installation Problems', link: '/troubleshooting/installation' }
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