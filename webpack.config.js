import defaultConfig from "@wordpress/scripts/config/webpack.config.js";
import DependencyExtractionWebpackPlugin from "@wordpress/dependency-extraction-webpack-plugin";
import { glob } from "glob";

/** @import { Configuration } from "webpack" */

const [scriptConfig, moduleConfig] = defaultConfig;

/** @type {Configuration} */
const sharedModuleConfig = {
    ...moduleConfig,
    entry: (() => {
        const res = {};

        for (const path of glob.sync('src/js/*.js')) {
            const name = path.slice(4, -3); // trim "src/" and ".js"
            res[name] = `./${path}`;
        }

        return res
    })(),
};

const dependencyExtractionWebpackPlugin = () =>
    new DependencyExtractionWebpackPlugin({
        requestToExternalModule(request) {
            if (request.startsWith('#nccr/')) {
                return request;
            }
        },
    });

/** @type {(config: Configuration) => Configuration} */
const extend = (config) => process.env.WP_NO_EXTERNALS ? config : {
    ...config,
    plugins: [
        ...config.plugins.filter((plugin) =>
            plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
        ),
        dependencyExtractionWebpackPlugin(),
    ],
};

export default [
    extend(scriptConfig),
    extend(moduleConfig),
    extend(sharedModuleConfig),
];