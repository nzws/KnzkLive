'use strict';

const ON = true;
const OFF = null;

module.exports = {
  extends: ['stylelint-config-standard', 'stylelint-config-prettier'],
  plugins: ['stylelint-scss'],
  rules: {
    'at-rule-no-unknown': OFF,
    'scss/at-rule-no-unknown': ON
  }
};
