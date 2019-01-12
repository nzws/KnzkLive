<div class="container<?php if(isset($navmode)) echo "-fluid"; ?> nav-container">
  <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #393f4f;">
    <a class="navbar-brand" href="<?=u("")?>">
      KnzkLive
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
      aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ml-auto">
        <?php
        if (!isset($my) && isset($_SESSION["acct"])) $my = getMe();
        if (isset($my["id"])) : ?>
            <?php if ($my["is_broadcaster"]) : ?>
                <form class="form-inline">
                  <a class="btn btn-outline-warning" href="<?=u("new")?>"><b>配信を<?=$my["live_current_id"] ? "管理" : "始める"?></b></a>
                </form>
            <?php endif; ?>
          <li class="nav-item dropdown active mr-sm-1">
            <a class="nav-link header_avatar_dropdown" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <img src="<?=$my["misc"]["avatar"]?>" class="avatar_img_navbar rounded-circle"/>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="#">
                <b><?=$my["name"]?></b><br>
                <small class="text-secondary"><?=$my["acct"]?></small>
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="<?=u("settings")?>">ユーザー設定</a>
              <a class="dropdown-item" href="<?=u("logout")?>">ログアウト</a>
            </div>
          </li>
        <?php else : ?>
          <form class="form-inline">
            Login:
            <button type="button" class="btn btn-outline-primary ml-2" data-toggle="modal" data-target="#loginModal"><b>Mastodon</b></button>
            <a class="btn btn-outline-info ml-2" href="<?=u("auth/twitter")?>"><b>Twitter</b></a>
          </form>
        <?php endif; ?>
        </ul>
    </div>
  </nav>
</div>

<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">ログイン</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text">Mastodon</span>
          </div>
          <input type="text" class="form-control" placeholder="ex) <?=$env["masto_login"]["domain"]?>" value="<?=$env["masto_login"]["domain"]?>" id="login_domain">
        </div>
        <small><?=$env["masto_login"]["domain"]?>以外のアカウントでログインすると一部のアカウントではコメントが表示できない可能性があります。<a href="<?=u("help")?>#help1" target="_blank">理由</a></small><br>
        <small>KnzkLiveではアクセストークンをデータベースに保管しません。また、認証とコメント以外に使用する事はありません。</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="location.href=`<?=u('login')?>?domain=` + document.getElementById('login_domain').value">ログイン</button>
      </div>
    </div>
  </div>
</div>
