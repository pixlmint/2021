const path = require('path')
const MiniCssExtractPlugin = require("mini-css-extract-plugin")

module.exports = {
  entry: {
    app: './assets/js/app.js',
    frontend: './assets/js/frontend.js',
    admin: './assets/js/admin.js',
    home: './assets/js/home.js',
    editHome: './assets/js/edit-home.js',
  },
  output: {
    path: path.resolve(__dirname, 'public/build'),
    filename: '[name].bundle.js',
    publicPath: '',
  },
  plugins: [
    new MiniCssExtractPlugin(),
  ],
  module: {
    rules: [
      {
        test: /.css$/,
        use: [MiniCssExtractPlugin.loader, 'css-loader'],
      },
      { test: /\.(jpe?g|gif|png|svg|woff|ttf|wav|mp3|ttf)$/, loader: "file-loader" }
    ]
  }
}
