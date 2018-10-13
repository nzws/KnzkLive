<!DOCTYPE html>
<html>
<head>
  <meta name="robots" content="noindex">
  <style>
    html,
    body {
      color: #f3f3f3;
      background: #212121;
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", YuGothic, "ヒラギノ角ゴ ProN W3", Hiragino Kaku Gothic ProN, Arial, "メイリオ", Meiryo, sans-serif;
      font-size: small;
      user-select: none;
    }

    body {
      width: 100%;
      height: 100%;
    }

    .center_v {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
    }

    .footer {
      position: absolute;
      bottom: 0;
      width: 100%;
    }

    .footer_content {
      padding: 10px;
    }

    img {
      width: 300px;
      max-width: 100%;
      animation: pulse 4s infinite;
    }

    @keyframes pulse {
      0% {
        opacity: 1;
      }

      50% {
        opacity: .2;
      }

      100% {
        opacity: 1;
      }
    }
  </style>
</head>
<body>
<div class="center_v">
  <img src="https://github.com/KnzkDev.png"/>
</div>
<div class="footer">
  <div class="footer_content">
    <b>KNZKLIVE</b> PLAYER · <span id="splash_loadtext">配信は終了しました。</span>
  </div>
</div>
</body>
</html>