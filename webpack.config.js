var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .disableSingleRuntimeChunk()
    .addEntry('app', './assets/js/app.js')
    .addEntry('stream', './assets/js/stream.js')
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .autoProvidejQuery()
    .addStyleEntry('style', './assets/css/app.scss')
    .addStyleEntry('stream-style', './assets/css/stream.css')
    .enableSassLoader()
;

module.exports = Encore.getWebpackConfig();
