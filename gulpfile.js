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
var sassOptions = {
	errLogToConsole: true,
	outputStyle: "expanded",
};
function sassProcess() {
	return src("./blocks/**/*.scss")
		.pipe(sass(sassOptions).on("error", sass.logError))
		.pipe(postcss(sassProcessors))
		.pipe(dest("./blocks"));
}
// Tasks which watch for changes in specified files/dirs and run tasks based on filetypes edited
function watchTask(done) {
	watch(["./src/scss/*.scss"], sassProcessSite);
	watch("./blocks/**/*.scss", sassProcess);
	watch(["./src/js/*.js"], series(javascriptLint, javascriptProcess));
	// Watch for changes to acf
	watch("acf/*.json", moveBlockJson);
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

function moveBlockJson(done) {
	/** Get All files within the acf folder
	 * Loop through each file, and look for the property location.value
	 * If location.param == 'block' then search in value for the text after the character '/'
	 * Save this value as a variable and then check if a folder exists in /blocks
	 * with the same name. If it does, move the file to that folder.
	 */
	// Get all files in acf-json
	files = fs.readdirSync("acf");
	let blocksFolder = "blocks";

	files.forEach(function (stream, file) {
		// Get the absolute path to the acf json file
		// Get relative path to the acf json file
		file = "acf/" + stream;
		if (stream != ".DS_Store") {
			// Convert the file into a json object
			var jsonContent = JSON.parse(fs.readFileSync(file));

			// If jsonContent.title contains 'Blocks > ' then we know it's a block
			let acfTitle = jsonContent.title;

			let blockName;

			if (acfTitle.includes("Block > ")) {
				blockName = acfTitle.split("Block > ")[1];
			} else if (acfTitle.includes("Block &gt; ")) {
				blockName = acfTitle.split("Block &gt; ")[1];
			}

			if (blockName) {
				// Remove 'and' from the block name
				blockName = blockName.replace("and", "");
				// Replace whitespace with hyphens and make lowercase
				blockName = blockName.replace(/\s+/g, "-").toLowerCase();
				// Check if the block folder exists
				if (fs.existsSync(blocksFolder + "/" + blockName)) {
					// Move the file to the block folder
					fs.renameSync(file, blocksFolder + "/" + blockName + "/" + stream);
				}
			}
		}
		done();
	});
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
