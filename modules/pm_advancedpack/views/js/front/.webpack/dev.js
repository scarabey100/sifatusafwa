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
const common = require('./common.js');

/**
 * Returns the development webpack config,
 * by merging development specific configuration with the common one.
 */
function devConfig() {
  const dev = Object.assign(
    common,
    {
      devtool: 'inline-source-map',
      devServer: {
        hot: true,
        contentBase: path.resolve(__dirname, '/../public'),
        publicPath: '/',
      },
    },
  );

  return dev;
}

module.exports = devConfig;
