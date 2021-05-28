const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { join } = require('path');
const Sass = require('sass');
const StyleLintPlugin = require('stylelint-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const WebpackBar = require('webpackbar');

module.exports = {
  mode: 'production',
  entry: './assets/js/index.js',
  output: { filename: 'bundle.js', path: join(__dirname, 'public/bundle') },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: [{ loader: 'babel-loader', options: { cacheDirectory: true } }]
      },
      {
        test: /\.(scss)$/,
        exclude: /node_modules/,
        use: [
          { loader: MiniCssExtractPlugin.loader },
          { loader: 'css-loader' },
          { loader: 'postcss-loader' },
          {
            loader: 'sass-loader',
            options: { implementation: Sass }
          }
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
    new MiniCssExtractPlugin({ filename: 'bundle.css' }),
    new StyleLintPlugin(),
    new WebpackBar({ profile: true, reporter: 'profile' })
  ],
  optimization: {
    minimizer: [new TerserPlugin({ parallel: true, extractComments: true })]
  },
  stats: 'errors-only'
};
