import js from '@eslint/js';
import globals from 'globals';
import reactHooks from 'eslint-plugin-react-hooks';
import react from 'eslint-plugin-react';

export default [
    js.configs.recommended,
    {
        files: ['resources/js/**/*.{js,jsx}'],
        plugins: {
            react,
            'react-hooks': reactHooks,
        },
        languageOptions: {
            globals: { ...globals.browser },
            parserOptions: {
                ecmaVersion: 'latest',
                ecmaFeatures: { jsx: true },
                sourceType: 'module',
            },
        },
        rules: {
            // Prevent debug leakage into production builds
            'no-console': ['warn', { allow: ['warn', 'error'] }],

            // React hooks correctness
            'react-hooks/rules-of-hooks': 'error',
            'react-hooks/exhaustive-deps': 'warn',

            // Not needed with the new JSX transform (React 17+)
            'react/jsx-uses-react': 'off',
            'react/react-in-jsx-scope': 'off',
        },
        settings: {
            react: { version: 'detect' },
        },
    },
];
