const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

module.exports = (env, argv) => {
  const isProduction = argv.mode === 'production';

  return {
    entry: './assets/src/index.js',
    output: {
      path: path.resolve(__dirname, 'assets/build'),
      filename: 'main.js',
      clean: true,
    },
    module: {
      rules: [
        {
          test: /\.(js|jsx)$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env', '@babel/preset-react'],
            },
          },
        },
        {
          test: /\.css$/,
          use: [
            isProduction ? MiniCssExtractPlugin.loader : 'style-loader',
            'css-loader'
          ],
        },
      ],
    },
    resolve: {
      extensions: ['.js', '.jsx'],
    },
    externals: {
      react: 'React',
      'react-dom': 'ReactDOM',
      '@wordpress/i18n': ['wp', 'i18n'],
    },
    plugins: isProduction ? [
      new MiniCssExtractPlugin({
        filename: 'main.css',
      }),
    ] : [],
    optimization: {
      minimizer: isProduction ? [
        `...`,
        new CssMinimizerPlugin(),
      ] : [],
    },
    devtool: isProduction ? false : 'source-map',
  };
};