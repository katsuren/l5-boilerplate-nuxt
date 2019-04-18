module.exports = {
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/resources/nuxt/$1',
    '^~/(.*)$': '<rootDir>/resources/nuxt/$1',
    '^vue$': 'vue/dist/vue.common.js'
  },
  moduleFileExtensions: ['js', 'vue', 'json'],
  transform: {
    '^.+\\.js$': 'babel-jest',
    '.*\\.(vue)$': 'vue-jest'
  },
  collectCoverage: true,
  collectCoverageFrom: [
    '<rootDir>/resources/nuxt/components/**/*.vue',
    '<rootDir>/resourcee/nuxt/pages/**/*.vue'
  ]
}
