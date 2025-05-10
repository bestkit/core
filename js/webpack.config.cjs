const config = require('bestkit-webpack');
const { merge } = require('webpack-merge');

module.exports = merge(config(), {
  output: {
    library: 'bestkit.core',
  },
});
