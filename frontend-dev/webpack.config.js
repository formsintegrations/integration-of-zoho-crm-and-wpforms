const path = require('path')
const webpack = require('webpack')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const { CleanWebpackPlugin } = require('clean-webpack-plugin')
const CopyPlugin = require('copy-webpack-plugin')
const WorkboxPlugin = require('workbox-webpack-plugin')
const TerserPlugin = require('terser-webpack-plugin')
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin')
const safePostCssParser = require('postcss-safe-parser')
const svgToMiniDataURI = require('mini-svg-data-uri')
// const BrowserSyncPlugin = require('browser-sync-webpack-plugin')
// const HtmlWebpackPlugin = require('html-webpack-plugin')
// const autoprefixer = require('autoprefixer');
// const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer')

module.exports = (env, argv) => {
  const production = argv.mode !== 'development'

  return {
    // devtool: production ? false : 'source-map',
    devtool: production ? false : 'eval',
    entry: {
      index: path.resolve(__dirname, 'src/index.js'),
      bitwpfzc: path.resolve(__dirname, 'src/resource/sass/app.scss'),
    },
    output: {
      filename: '[name].js',
      ...production && { pathinfo: false },
      path: path.resolve(__dirname, '../assets/js/'),
      chunkFilename: production ? '[name].js?v=[contenthash:6]' : '[name].js',
      library: '_bitwpfzc',
      libraryTarget: 'umd',
      // publicPath: '/',
    },
    /* devServer: {
      open: true,
      writeToDisk: true,
      // path: path.resolve(__dirname, '../assets/'),
      // inline: true,
      // host: 'bitcode.io',
      /*  overlay: {
         warnings: true,
         errors: true,
       }, */
    // contentBase: path.resolve(__dirname, '../assets/js'),
    // path: path.resolve(__dirname, '../assets/js/'),
    // publicPath: path.resolve(__dirname, '../assets/'),
    // public: 'http://bitcode.io/wp-admin/admin.php?page=bitform#',

    /*
    proxy: [
      {
        path: path.resolve(__dirname, '../assets/'),
        // path: 'http://bitcode.io/wp-admin/admin.php?page=bitform#',
        target: 'http://bitcode.io/',
      },
    ],
  }, */
    devServer: {
      // open: true,
      // hotOnly: true,
      hot: true,
      // host: 'blank-site-php.io',
      // port: 80,
      // writeToDisk: true,
      contentBase: path.resolve(__dirname, '../assets/'),
      // contentBasePublicPath: '/js',
      // publicPath: 'http://blank-site-php.io/',
      // allowedHosts: ["blank-site-php.io"],
      headers: { 'Access-Control-Allow-Origin': '*' },
      // disableHostCheck: true,
      /* proxy: {
        '/': {
          target: 'http://blank-site-php.io:80',
          // publicPath: '/wp-content/plugins/BitForm/assets/js',
          secure: false,
          changeOrigin: true,
          autoRewrite: true,
          headers: {
            'X-ProxiedBy-Webpack': true,
          },
        },
      }, */
      // publicPath: path.resolve(__dirname, '../assets/js/'),
    },
    /*  performance: {
       hints: 'error',
       maxAssetSize: 100 * 1024, // 100 KiB
       maxEntrypointSize: 100 * 1024, // 100 KiB
     }, */
    optimization: {
      runtimeChunk: 'single',
      splitChunks: {
        cacheGroups: {
          main: {
            test: /[\\/]node_modules[\\/]/,
            name: 'vendors-main',
            chunks: chunk => chunk.name === 'index',
          },
          gutenbergBlock: {
            test: /[\\/]node_modules[\\/]/,
            name: 'vendors-block',
            chunks: chunk => chunk.name === 'bitwpfzc-shortcode-block',
          },
        },
      },
      minimize: production,
      minimizer: [
        ...(!production ? [] : [
          new TerserPlugin({
            terserOptions: {
              parse: { ecma: 8 },
              compress: {
                drop_console: production,
                ecma: 5,
                warnings: false,
                comparisons: false,
                inline: 2,
              },
              mangle: { safari10: true },
            },
            extractComments: {
              condition: true,
              filename: (fileData) => `${fileData.filename}.LICENSE.txt${fileData.query}`,
              banner: (commentsFile) => `BitPress license information ${commentsFile}`,
            },
          }),
          new OptimizeCSSAssetsPlugin({
            cssProcessorOptions: {
              parser: safePostCssParser,
              map: production ? false : { inline: false, annotation: true },
            },
            cssProcessorPluginOptions: { preset: ['default', { minifyFontValues: { removeQuotes: false } }] },
          }),
        ]),
      ],
    },
    plugins: [
      /* ...(production ? [] : [
        new webpack.DllReferencePlugin({
          name: '_bitwpfzc',
          context: '/',
          manifest: path.resolve(__dirname, 'dll-plugin-manifest.json'),
        }),
      ]), */
      // new BundleAnalyzerPlugin(),
      new CleanWebpackPlugin(),
      new webpack.DefinePlugin({ 'process.env': { NODE_ENV: production ? JSON.stringify('production') : JSON.stringify('development') } }),
      new MiniCssExtractPlugin({
        filename: '../css/[name].css?v=[contenthash:6]',
        ignoreOrder: true,
      }),
      new CopyPlugin({
        patterns: [
          {
            from: path.resolve(__dirname, 'public/wp_index.html'),
            to: path.resolve(__dirname, '../views/view-root.php'),
          },
          {
            from: path.resolve(__dirname, 'manifest.json'),
            to: path.resolve(__dirname, '../assets/js/manifest.json'),
          },
          {
            from: path.resolve(__dirname, 'bitform-logo-icon.ico'),
            to: path.resolve(__dirname, '../assets/img/bitform-logo-icon.ico'),
          },
          {
            from: path.resolve(__dirname, 'bit-form.png'),
            to: path.resolve(__dirname, '../assets/img/bit-form.png'),
          },
          {
            from: path.resolve(__dirname, 'logo-bg.svg'),
            to: path.resolve(__dirname, '../assets/img/logo-bg.svg'),
          },
          {
            from: path.resolve(__dirname, 'logo.svg'),
            to: path.resolve(__dirname, '../assets/img/logo.svg'),
          },
          {
            from: path.resolve(__dirname, 'redirect.php'),
            to: path.resolve(__dirname, '../assets/js/index.php'),
          },
        ],
      }),
      // new BrowserSyncPlugin({
      //   // browse to http://localhost:3000/ during development,
      //   // ./public directory is being served
      //   host: 'localhost',
      //   port: 3000,
      //   // files: ['.php'],
      //   // server: { baseDir: [path.resolve(__dirname, '../assets/')] },
      //   proxy: 'http://bitcode.io',
      //   files: [{
      //     match: [
      //       '.php',
      //       // '**/*.php'
      //     ],
      //     fn(event, file) {
      //       if (event === 'change') {
      //         const bs = require('browser-sync').get('bs-webpack-plugin')
      //         bs.reload()
      //       }
      //     },
      //   }],
      // }, { reload: false }),
      ...(!production ? [] : [
        new WorkboxPlugin.GenerateSW({
          clientsClaim: production,
          skipWaiting: production,
          dontCacheBustURLsMatching: /\.[0-9a-f]{8}\./,
          exclude: [/\.map$/, /asset-manifest\.json$/, /LICENSE/, /view-root.php/],
        })]),
    ],

    resolve: { extensions: ['.js', '.jsx', '.json', '.css'] },

    module: {
      strictExportPresence: true,
      rules: [
        {
          test: /\.(jsx?)$/,
          exclude: /node_modules/,
          loader: 'babel-loader',
          // include: path.resolve(__dirname, 'src'),
          options: {
            presets: [
              [
                '@babel/preset-env',
                {
                  // useBuiltIns: 'entry',
                  // corejs: 3,
                  targets: {
                    // browsers: ['>0.2%', 'ie >= 9', 'not dead', 'not op_mini all'],
                    browsers: !production ? ['Chrome >= 88'] : ['>0.2%', 'ie >= 11'],
                  },
                  loose: true,
                },
              ],
              ['@babel/preset-react', { runtime: 'automatic' }],
            ],
            plugins: [
              '@babel/plugin-transform-runtime',
              ['@babel/plugin-transform-react-jsx', { runtime: 'automatic' }],
              ['@babel/plugin-proposal-class-properties', { loose: true }],
              ['@babel/plugin-proposal-private-methods', { loose: true }],
              ['@wordpress/babel-plugin-makepot', { output: path.resolve(__dirname, 'locale.pot') }],
              // "@babel/plugin-transform-regenerator",
            ],
          },
        },
        {
          test: /\.(sa|sc|c)ss$/,
          exclude: /node_modules/,
          use: [
            // 'style-loader',
            {
              loader: MiniCssExtractPlugin.loader,
              options: { publicPath: '' },
            },
            { loader: 'css-loader' },
            {
              loader: 'postcss-loader',
              options: {
                postcssOptions: {
                  plugins: [
                    ['autoprefixer'],
                  ],
                },
              },
            },
            {
              loader: 'sass-loader',
              options: {
                sassOptions: {
                  ...production && { outputStyle: 'compressed' },
                  ...!production && { sourceMap: false },
                },
              },
            },
          ],
        },
        {
          test: /\.css$/,
          include: /node_modules/,
          use: ['style-loader', 'css-loader'],
        },
        {
          test: /\.svg$/i,
          use: [
            {
              loader: 'url-loader',
              options: {
                options: { generator: (content) => svgToMiniDataURI(content.toString()) },
                name: production ? '[name][contenthash:8].[ext]' : '[name].[ext]',
                outputPath: '../img',
              },
            },
          ],
        },
        {
          test: /\.(jpe?g|png|gif|ttf|woff|woff2|eot)$/i,
          use: [
            {
              loader: 'url-loader',
              options: {
                limit: 3000,
                name: production ? '[name][contenthash:8].[ext]' : '[name].[ext]',
                outputPath: '../img',
              },
            },
          ],
        },
      ],
    },
    externals: {
      '@wordpress/i18n': {
        commonjs: ['wp', 'i18n'],
        commonjs2: ['wp', 'i18n'],
        amd: ['wp', 'i18n'],
        root: ['wp', 'i18n'],
      },
    },
  }
}
