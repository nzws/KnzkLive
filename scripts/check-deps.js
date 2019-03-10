'use strict';

const pkg = require('../package.json');
const chalk = require('chalk');
const logSymbols = require('log-symbols');

function validateDependencyObject(object) {
  Object.keys(object).forEach(key => {
    if (object[key][0] === '^' || object[key][0] === '~') {
      console.error(
        logSymbols.error,
        `Dependency ${chalk.bold.bgRed(key)} should be pinned.`
      );
      process.exitCode = 1;
    }
  });
}

validateDependencyObject(pkg.dependencies);
validateDependencyObject(pkg.devDependencies);
