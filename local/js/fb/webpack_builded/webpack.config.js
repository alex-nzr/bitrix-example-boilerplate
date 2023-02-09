const path = require("path")
const {CleanWebpackPlugin} = require('clean-webpack-plugin')

const isDev = process.env.NODE_ENV === 'development';

const filename = (ext => `[name].bundle.${ext}`)

module.exports = {
    mode: "development",
    devServer: {
        historyApiFallback: true,
        liveReload: true,
        watchFiles:  ['src/**/*', 'public/**/*'],
        open: true,
        compress: true,
        //hot: true,
        port: 8080,
    },
    devtool: 'source-map',//isDev ? 'source-map' : false,
    resolve: {
        extensions: ['.html','.js','.ts','.jsx','.tsx','.scss'],
        alias: {
            '@root': path.resolve(__dirname, './src'),
        }
    },
    entry: {
        popup: path.resolve(__dirname, './src/scripts/index.ts'),
    },
    output: {
        filename: filename('js'),
        path: path.resolve(__dirname, "./dist")
    },
    plugins:[
        new CleanWebpackPlugin({}),
    ],
    module:  {
        rules: [
            {
                test: /\.(ts|js|jsx|tsx)$/,
                exclude: /node_modules/,
                use: [
                    {
                        loader: 'babel-loader',
                        options: {
                            presets: [
                                '@babel/preset-env',
                                "@babel/preset-typescript",
                                "@babel/preset-react"
                            ],
                            plugins: ["@babel/transform-runtime"],
                            targets: "> 0.25%, not dead"
                        }
                    }
                ],
            },
            {
                test: /\.(scss|css)$/,
                use: [
                    'style-loader',
                    {
                        loader: 'css-loader',
                        options: {
                            modules: true
                        }
                    },
                    'postcss-loader',
                    'sass-loader'
                ],
            },
            {
                test: /\.(?:ico|gif|png|jpg|jpeg)$/i,
                type: 'asset/resource',
            },
            {
                test: /\.(woff(2)?|eot|ttf|otf|svg|)$/,
                type: 'asset/inline',
            },
        ]
    }
}