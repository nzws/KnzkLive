'use strict';

const npm = require('../package.json');
const composer = require('../composer.json');
const chalk = require('chalk');
const logSymbols = require('log-symbols');

function validateDependencyObject(object) {
  const dependencies = Object.values(object);

  dependencies.forEach(dependency => {
    if (dependency[0] === '^' || dependency[0] === '~') {
      console.error(
        logSymbols.error,
        `Dependency ${chalk.bold.bgRed(dependency)} should be pinned.`
      );
      process.exitCode = 1;
    }
  });
}

validateDependencyObject(npm.dependencies);
validateDependencyObject(npm.devDependencies);
validateDependencyObject(composer.require);
