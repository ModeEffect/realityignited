const defaultConfig = require('./node_modules/@wordpress/scripts/config/webpack.config');
const { sync: glob } = require('fast-glob');
const { merge } = require('webpack-merge');
const path = require('path');

module.exports = merge(defaultConfig, {
  entry: glob('./js/blocks/**/index.js'),
  output: {
    filename: 'blocks.js',
    path: path.resolve(__dirname, 'public/build'),
  },
  devServer: {
    allowedHosts: 'all',
    liveReload: false,
  },
});
