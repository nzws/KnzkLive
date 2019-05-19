module.exports = () => {
  window.requestAnimationFrame = (() =>
    window.requestAnimationFrame ||
    window.webkitRequestAnimationFrame ||
    window.mozRequestAnimationFrame ||
    window.msRequestAnimationFrame ||
    window.oRequestAnimationFrame ||
    (f => window.setTimeout(f, 1000 / 120)))();

  document.hidden = (() =>
    document.hidden || document.webkitHidden || document.msHidden)();
};
