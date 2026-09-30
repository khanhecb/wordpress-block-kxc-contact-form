<?php

return [
    'dependencies' => [
        'wp-blocks',
        'wp-element',
        'wp-editor',
        'wp-block-editor',
        'wp-components',
        'wp-server-side-render' 
    ],
    'version' => (string) filemtime(__DIR__ . '/editor.js'),
];
