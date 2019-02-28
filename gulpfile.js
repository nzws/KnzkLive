const { src, dest, parallel } = require('gulp');
const sass = require('gulp-sass');

function css() {
  return src('assets/scss/*.scss')
    .pipe(
      sass({
        outputStyle: 'compressed'
      })
    )
    .pipe(dest('public/bundle'));
}

function js() {
  return src('assets/js/*.js', { sourcemaps: true })
    .pipe(concat('app.min.js'))
    .pipe(dest('build/js', { sourcemaps: true }));
}

exports.js = js;
exports.css = css;
exports.default = parallel(css);
