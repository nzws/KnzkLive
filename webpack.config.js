const path = require('path');
const glob = require('glob');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const StyleLintPlugin = require('stylelint-webpack-plugin');
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
        test: /\.(scss)$/,
        exclude: /node_modules/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'postcss-loader',
          'sass-loader'
        ]
      },
      {
        test: /\.(ttf|eot|woff|woff2|svg)$/,
        use: [{ loader: 'file-loader', options: { name: '[name].[ext]' } }]
      }
    ]
  },
  plugins: [
    new CleanWebpackPlugin(),
    new StyleLintPlugin(),
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
