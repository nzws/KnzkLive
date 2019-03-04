const path = require('path');
const glob = require('glob');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const WebpackBar = require('webpackbar');

module.exports = {
  mode: 'production',
  entry: './assets/js/index.js',
  output: {
    filename: 'bundle.js',
    path: path.join(__dirname, 'public/bundle')
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: ['babel-loader?cacheDirectory']
      },
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'postcss-loader',
          'sass-loader'
        ]
      }
    ]
  },
  plugins: [
    new CleanWebpackPlugin('public/bundle'),
    new WebpackBar({ profile: true, reporter: 'profile' })
  ],
  optimization: {
    minimizer: [
      new TerserPlugin({
        cache: true,
        parallel: true,
        terserOptions: { output: { comments: false } }
      }),
      new MiniCssExtractPlugin({ filename: 'bundle.css' })
    ]
  },
  stats: 'errors-only'
};
