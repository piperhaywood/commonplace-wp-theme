var gulp = require("gulp");
var browserSync = require("browser-sync").create();
var sass = require("gulp-sass");
var autoprefixer = require("gulp-autoprefixer");
var sourcemaps = require("gulp-sourcemaps");
var cleanCSS = require("gulp-clean-css");
var concat = require("gulp-concat");
var uglify = require("gulp-uglify-es").default;
var header = require("gulp-header");

const arg = ((argList) => {
  let arg = {},
    a,
    opt,
    thisOpt,
    curOpt;
  for (a = 0; a < argList.length; a++) {
    thisOpt = argList[a].trim();
    opt = thisOpt.replace(/^\-+/, "");

    if (opt === thisOpt) {
      // argument value
      if (curOpt) arg[curOpt] = opt;
      curOpt = null;
    } else {
      // argument name
      curOpt = opt;
      arg[curOpt] = true;
    }
  }

  return arg;
})(process.argv);

var pkg = require("./package.json");
var banner = [
  "/**",
  " * Theme Name: <%= pkg.title %>",
  " * Author: <%= pkg.author.name %>",
  " * Author URI: <%= pkg.author.url %>",
  " * Description: <%= pkg.description %>",
  " * Version: <%= pkg.version %>",
  " * License: <%= pkg.license %>",
  " * Textdomain: <%= pkg.name %>",
  " */",
  "",
].join("\n");

var themeDir = pkg.name + "/";

gulp.task("sass", function () {
  return gulp
    .src("css/**/*.scss")
    .pipe(sourcemaps.init())
    .pipe(sass())
    .pipe(sourcemaps.write())
    .pipe(autoprefixer())
    .pipe(concat("style.css"))
    .pipe(cleanCSS())
    .pipe(header(banner, { pkg: pkg }))
    .pipe(gulp.dest(themeDir))
    .pipe(
      browserSync.reload({
        stream: true,
      })
    );
});

gulp.task("js", function () {
  return gulp
    .src(["node_modules/prismjs/prism.js", "js/scripts.js"])
    .pipe(sourcemaps.init())
    .pipe(concat("scripts.js"))
    .pipe(uglify())
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(themeDir));
});

gulp.task("customizer", function () {
  return gulp
    .src(["js/customizer.js"])
    .pipe(sourcemaps.init())
    .pipe(concat("customizer.js"))
    .pipe(uglify())
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(themeDir));
});

gulp.task("watch", function (done) {
  gulp.watch("css/**/*.scss", gulp.series("sass"));
  gulp
    .watch("js/scripts.js", gulp.series("js"))
    .on("change", browserSync.reload);
  gulp
    .watch("js/customizer.js", gulp.series("customizer"))
    .on("change", browserSync.reload);
  gulp.watch(themeDir + "**/*.php").on("change", browserSync.reload);
  done();
});

gulp.task("browserSync", function (done) {
  var proxy = arg.proxy ? arg.proxy : "localhost:8888/";
  browserSync.init({
    ghostMode: false,
    open: true,
    notify: false,
    proxy: proxy,
  });
  done();
});

gulp.task("default", gulp.parallel("sass", "js", "customizer"));
gulp.task("dev", gulp.series("default", gulp.parallel("watch", "browserSync")));
