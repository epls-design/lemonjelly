require("es6-promise").polyfill();

// Set up required modules
const { parallel, series, src, dest, watch } = require("gulp");
const rename = require("gulp-rename");
const banner = require("gulp-banner");
const pkg = require("./package.json");
const fs = require("fs");
const path = require("path");
const glob = require("glob");

var eslint = require("gulp-eslint");
var uglify = require("gulp-uglify");
var concat = require("gulp-concat");
var sass = require("gulp-sass")(require("sass"));
var postcss = require("gulp-postcss");
var pxtorem = require("postcss-pxtorem");
var autoprefixer = require("autoprefixer");
var cssnano = require("cssnano");

// Processors used by postcss
var sassProcessors = [
	autoprefixer(),
	pxtorem({
		rootValue: 16,
		unitPrecision: 2, // Decimal places
		propList: ["*"], // Apply to all elements
		replace: true, // False enables px fallback
		mediaQuery: false, // Do not apply within media queries (we use em instead)
		minPixelValue: 0,
	}),
	cssnano(),
];

// Sets options which are used later on in this file
const opts = {
	bannerText:
		"/* \n" +
		" *  Theme Name: <%= pkg.friendly_name %>\n" +
		" *  Theme URI: <%= pkg.homepage %>\n" +
		" *  Author: <%= pkg.author.name %> <<%= pkg.author.email %>>\n" +
		" *  Author URI: <%= pkg.author.url %>\n" +
		" *  Description: <%= pkg.description %>\n" +
		" *  Tags: <%= pkg.keywords %>\n" +
		" *  Template: jellypress \n" +
		" *  Version: <%= pkg.version %>\n" +
		" *  License: <%= pkg.license %>\n" +
		" *  Text Domain: <%= pkg.text_domain %> */ \n",
};

// Tasks which watch for changes in specified files/dirs and run tasks based on filetypes edited
function watchTask(done) {
	watch(["./src/scss/*.scss"], sassProcessSite);

	watch(["./src/js/*.js"], series(javascriptLint, javascriptProcess));
	done();
}

// eslint all first party JS
function javascriptLint(done) {
	return src(["./src/js/*.js"]).pipe(eslint()).pipe(eslint.format());
	done();
}

// Tasks which process the core javascript files
function javascriptProcess() {
	return src(["./src/js/*.js"])
		.pipe(concat("theme.min.js"))
		.pipe(uglify({ mangle: true }))
		.pipe(dest("./js/"));
}

// Process Theme Sass
function sassProcessSite() {
	return src("./src/scss/compile.scss")
		.pipe(sass())
		.pipe(postcss(sassProcessors))
		.pipe(rename("style.css"))
		.pipe(
			banner(opts.bannerText, {
				pkg: pkg,
			})
		)
		.pipe(dest("./"));
}

// Tasks which run on $ gulp build
const buildScripts = series(
	parallel(sassProcessSite, series(javascriptLint, javascriptProcess))
);

// Tasks which run on $ gulp
const serverScripts = parallel(watchTask);

exports.watch = watchTask;
exports.build = buildScripts;
exports.default = serverScripts;
exports.init = series(buildScripts, serverScripts);
