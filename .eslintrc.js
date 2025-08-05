module.exports = {
    env: {
        browser: true,
        es2021: true,
        node: true,
    },
    extends: [
        'eslint:recommended',
    ],
    parserOptions: {
        ecmaVersion: 'latest',
        sourceType: 'module',
    },
    overrides: [
        {
            files: ['*.vue'],
            parser: 'vue-eslint-parser',
            parserOptions: {
                parser: '@babel/eslint-parser',
                sourceType: 'module',
            },
            extends: [
                'plugin:vue/vue3-essential',
            ],
            rules: {
                'vue/multi-word-component-names': 'off',
            },
        },
    ],
}; 