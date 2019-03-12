'use strict';

const npm = require('../package.json');
const composer = require('../composer');
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

validateDependencyObject(npm.dependencies);
validateDependencyObject(npm.devDependencies);
validateDependencyObject(composer.require);
