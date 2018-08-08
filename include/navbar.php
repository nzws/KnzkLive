<div class="container<?php if(isset($navmode)) echo "-fluid"; ?>" style="margin: 10px auto;">
  <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #393f4f;border-radius: 5px">
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
                  <a class="btn btn-outline-warning" href="<?=u("new")?>"><b>配信を始める</b></a>
                </form>
            <?php endif; ?>

          <li class="nav-item dropdown active mr-sm-1">
            <a class="nav-link" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <b><?=$my["name"]?></b>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="<?=u("logout")?>">ログアウト</a>
            </div>
          </li>
        <?php else : ?>
            <form class="form-inline">
              <a class="btn btn-outline-warning" href="<?=u("login")?>"><b>Knzk.meでログイン</b></a>
            </form>
        <?php endif; ?>
        </ul>
    </div>
  </nav>
</div>