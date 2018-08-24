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
        if (!$my && $_SESSION["acct"]) $my = getMe();
        if ($my) : ?>
            <?php if ($my["isLive"]) : ?>
                <form class="form-inline">
                  <a class="btn btn-outline-warning" href="<?=u("new")?>"><b>配信を<?=$my["liveNow"] ? "管理" : "始める"?></b></a>
                </form>
            <?php endif; ?>
          <li class="nav-item dropdown active mr-sm-1">
            <a class="nav-link header_avatar_dropdown" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <img src="<?=$my["misc"]["avatar"]?>" class="avatar_img_navbar rounded-circle"/>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="#" disabled><b><?=$my["name"]?></b></a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="<?=u("settings")?>">ユーザー設定</a>
              <a class="dropdown-item" href="<?=u("logout")?>">ログアウト</a>
            </div>
          </li>
        <?php else : ?>
            <form class="form-inline">
              <a class="btn btn-outline-warning" href="<?=u("login")?>"><b><?=$env["masto_login"]["domain"]?>でログイン</b></a>
            </form>
        <?php endif; ?>
        </ul>
    </div>
  </nav>
</div>