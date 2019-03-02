const { src, dest, parallel, series, watch } = require('gulp');
const sass = require('gulp-sass');
const browserify = require('browserify');
const source = require('vinyl-source-stream');
const composer = require('gulp-uglify/composer');
const uglifyES = require('uglify-es');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');

function css() {
  return src('assets/scss/*.scss')
    .pipe(
      sass({
        outputStyle: 'compressed'
      })
    )
    .pipe(postcss([autoprefixer()]))
    .pipe(dest('public/bundle'));
}

function js() {
  return browserify({
    entries: ['./assets/js/index.js']
  })
    .bundle()
    .pipe(source('bundle.js'))
    .pipe(dest('public/bundle'));
}

function minifyjs() {
  return src('public/bundle/bundle.js')
    .pipe(composer(uglifyES, console)())
    .pipe(dest('public/bundle'));
}

exports.js = js;
exports.watch = watch('assets/**/*.*', parallel(js, css));
exports.build = series(css, js, minifyjs);
exports.default = exports.build;
