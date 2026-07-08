/**
 *
 * Advanced Pack
 *
 * @author Presta-Module.com <support@presta-module.com>
 * @copyright Presta-Module
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 *
 ****/
const path = require('path');
const webpack = require('webpack');
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const FixStyleOnlyEntriesPlugin = require('webpack-remove-empty-scripts');

module.exports = {
  externals: {
    jquery: 'jQuery',
  },
  entry: {
    bundles: './bundles',
  },
  output: {
    path: path.resolve(__dirname, '../public'),
    filename: '[name].bundle.js',
    libraryTarget: 'window',
    library: '[name]',

    sourceMapFilename: '[name].[hash:8].map',
    chunkFilename: '[id].[hash:8].js',
  },
  resolve: {
    extensions: ['.js'],
    alias: {
      '@js': path.resolve(__dirname, '../js'),
      '@node_modules': path.resolve(__dirname, '../node_modules'),
    },
  },
  module: {
  },
  plugins: [
    new FixStyleOnlyEntriesPlugin(),
    new CleanWebpackPlugin({
      cleanOnceBeforeBuildPatterns: ['!theme.rtlfix'],
    }),
    new webpack.ProvidePlugin({
      $: 'jquery', // needed for jquery-ui
      jQuery: 'jquery',
    }),
  ],
};
