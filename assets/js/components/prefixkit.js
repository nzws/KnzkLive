module.exports = () => {
  window.requestAnimationFrame = (() => {
    return (
      window.requestAnimationFrame ||
      window.webkitRequestAnimationFrame ||
      window.mozRequestAnimationFrame ||
      window.msRequestAnimationFrame ||
      window.oRequestAnimationFrame ||
      (f => {
        return window.setTimeout(f, 1000 / 120);
      })
    );
  })();
};
