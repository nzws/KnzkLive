'use strict';

const tasks = arr => arr.join(' && ');

module.exports = {
  hooks: {
    'pre-commit': tasks(['lint-staged', 'pretty-quick --staged']),
    'pre-push': tasks(['yarn check:deps', 'yarn check:audit'])
  }
};
