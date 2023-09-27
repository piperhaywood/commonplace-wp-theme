const gulp = require("gulp");
const browserSync = require("browser-sync").create();
const sass = require("gulp-sass")(require("sass"));
const autoprefixer = require("gulp-autoprefixer");
const sourcemaps = require("gulp-sourcemaps");
const cleanCSS = require("gulp-clean-css");
const concat = require("gulp-concat");
const uglify = require("gulp-uglify-es").default;
const header = require("gulp-header");

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

const pkg = require("./package.json");
const banner = [
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

const themeDir = pkg.name + "/";

gulp.task("sass", function () {
  return gulp
    .src("css/main.scss")
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

gulp.task("editorSass", function () {
  return gulp
    .src("css/editor.scss")
    .pipe(sourcemaps.init())
    .pipe(sass())
    .pipe(sourcemaps.write())
    .pipe(autoprefixer())
    .pipe(concat("editor.css"))
    .pipe(cleanCSS())
    .pipe(gulp.dest(themeDir + "assets/"))
    .pipe(
      browserSync.reload({
        stream: true,
      })
    );
});

gulp.task("js", function () {
  return gulp
    .src(["js/prism.js", "js/scripts.js"])
    .pipe(sourcemaps.init())
    .pipe(concat("scripts.js"))
    .pipe(uglify())
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(themeDir));
});

gulp.task("watch", function (done) {
  gulp.watch("css/**/*.scss", gulp.series("sass", "editorSass"));
  gulp
    .watch("js/scripts.js", gulp.series("js"))
    .on("change", browserSync.reload);
  gulp.watch(themeDir + "**/*.php").on("change", browserSync.reload);
  done();
});

gulp.task("browserSync", function (done) {
  const proxy = arg.proxy ? arg.proxy : "wordpress-unit-test.test";
  browserSync.init({
    ghostMode: false,
    open: true,
    notify: false,
    proxy: proxy,
  });
  done();
});

gulp.task("default", gulp.parallel("sass", "editorSass", "js"));
gulp.task("dev", gulp.series("default", gulp.parallel("watch", "browserSync")));
