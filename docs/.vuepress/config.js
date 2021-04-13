module.exports = {
    title: 'Streaming Media Craft Plugin',
    description: 'A Craft Plugin for working with (externally hosted) Streaming Media',
    base: '/craft-streaming-media-plugin/',
    themeConfig: {
        nav: [
            { text: 'Home', link: '/' },
            { text: 'Setup', link: '/setup/' },
            { text: 'Working with Streaming Media', link: '/streaming-media/' },
            { text: 'Stud I/O', link: 'https://stud-io.com' }
        ],
        sidebar: {
            '/setup/': [
                {
                    title: 'Installation',
                    collapsable: false,
                    sidebarDepth: 1,
                    children: [
                        ''
                    ]
                },
                {
                    title: 'Configure Backends',
                    collapsable: false,
                    children: [ 
                        'backend-cloudflare'
                    ],
                },
                'advanced-configuration'
            ],
            '/streaming-media/': [
                '',
                'adding-stream-assets',
                'rendering-stream-assets'
            ]
        }
    }
}