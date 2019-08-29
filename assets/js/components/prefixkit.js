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

  document.exitFullscreen = (() =>
    document.webkitCancelFullScreen ||
    document.mozCancelFullscreen ||
    document.msExitFullscreen ||
    document.exitFullscreen)();

  document.fullscreenElement = (() =>
    document.webkitFullscreenElement ||
    document.mozFullScreenElement ||
    document.msFullScreenElement ||
    document.fullscreenElement)();
};
