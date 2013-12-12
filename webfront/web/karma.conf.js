// Karma configuration
// Generated on Fri Sep 27 2013 16:37:01 GMT+0400 (MSK)

module.exports = function(config) {
    config.set({

        // base path, that will be used to resolve files and exclude
        basePath: '',


        // frameworks to use
        frameworks: ['jasmine', 'requirejs'],


        // list of files / patterns to load in the browser
        files: [
            'karma.main.js',
            {pattern: 'kit/**/*.html', included: false},
            {pattern: 'kit/config.js', included: false},
            {pattern: 'kit/**/*.js', included: false},
            {pattern: 'utils/**/*.js', included: false},
            {pattern: 'libs/**/*.js', included: false},
            {pattern: 'blocks/**/*.js', included: false},
            {pattern: 'blocks/**/*.html', included: false},
            {pattern: 'nls/**/*.js', included: false}
        ],


        // list of files to exclude
        exclude: [
            'kit/docs/**/*.js',
            'coverage/**/*.js',
            'main.js'
        ],


        // test results reporter to use
        // possible values: 'dots', 'progress', 'junit', 'growl', 'coverage'
        reporters: ['dots', 'coverage'],

        preprocessors: {
            // source files, that you wanna generate coverage for
            // do not include tests or libraries
            // (these files will be instrumented by Istanbul),
            'kit/core/**/!(*.spec.js)*.js': 'coverage',
            'kit/utils/**/!(*.spec.js)*.js': 'coverage'
        },

        coverageReporter: {
            type: 'html',
            dir: './build/coverage/'
        },


        // web server port
        port: 9876,


        // enable / disable colors in the output (reporters and logs)
        colors: true,


        // level of logging
        // possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
        logLevel: config.LOG_INFO,


        // enable / disable watching file and executing tests whenever any file changes
        autoWatch: false,


        // Start these browsers, currently available:
        // - Chrome
        // - ChromeCanary
        // - Firefox
        // - Opera
        // - Safari (only Mac)
        // - PhantomJS
        // - IE (only Windows)
        browsers: ['Chrome'],


        // If browser does not capture in given timeout [ms], kill it
        captureTimeout: 60000,


        // Continuous Integration mode
        // if true, it capture browsers, run tests and exit
        singleRun: false
    });
};
