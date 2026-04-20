const { defineConfig } = require("@vue/cli-service");

module.exports = defineConfig({
    transpileDependencies: true,
    chainWebpack: config => {
        config.optimization.splitChunks(false);
        config.output.filename('[name].js');
        // Disable persistent cache to prevent stale compiled chunks
        config.cache(false);
        config.module.rule('js').uses.delete('cache-loader');
        config.module.rule('ts').uses.delete('cache-loader');
        config.module.rule('tsx').uses.delete('cache-loader');
    },
    css: {
        extract: {
            filename: '[name].css',
            chunkFilename: '[name].css',
        },
    },
});
