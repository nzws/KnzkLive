<?php if ($env["is_testing"]) : ?>
<div style="background:red;color:#fff" class="is_debug">現在開発モードです。これが公開サーバーである場合はコンフィグファイルを確認してください。</div>
<?php endif; ?>
<?php
if (empty($_SESSION["UA_CONF"])) {
    $_SESSION["UA_CONF"] = serialize(UAParser\Parser::create()->parse($_SERVER['HTTP_USER_AGENT']));
}
$ua = unserialize($_SESSION["UA_CONF"]);
if ($ua->ua->family === "Safari" || $ua->os->family === "iOS") :
?>
<div class="alert alert-danger alert-dismissible fade show rounded-0" role="alert">
    <b>mac Safari</b> または <b>iOS全般</b> の環境ではKnzkLiveを正常にご利用頂く事ができません。別の端末・ブラウザにてお試しください。<br>
    <small>これらの環境でないにも関わらず表示されていますか？<a href="https://github.com/KnzkDev/KnzkLive/issues/new" target="_blank">私達に教えて下さい</a>。</small>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if (!empty($env["announcement"]["date"])) : ?>
<div class="alert alert-info alert-dismissible fade show rounded-0" role="alert" id="announcement_alert">
    <b><?=$env["announcement"]["date"]?>:</b>
    <?php if (!empty($env["announcement"]["link"])) : ?>
        <a href="<?=s($env["announcement"]["link"])?>" target="_blank"><?=$env["announcement"]["text"]?></a>
    <?php else : ?>
        <?=$env["announcement"]["text"]?>
    <?php endif; ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="localStorage['accouncement'] = '<?=s($env["announcement"]["date"])?>';">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<script>
    if (localStorage['accouncement'] === '<?=s($env["announcement"]["date"])?>') document.getElementById('announcement_alert').className = "invisible";
</script>
<?php endif; ?>
<?php if (!empty($toppage)) : ?>
<div>
    <div class="row mx-auto bg-dark d-flex align-items-center justify-content-between navbar" style="background-color: #393f4f;">
<?php else : ?>
<div class="container<?=isset($navmode) ? $navmode : null?> nav-container wide_hide">
    <div class="row rounded mx-auto bg-dark d-flex align-items-center justify-content-between navbar" style="background-color: #393f4f;">
<?php endif; ?>
        <a class="col-auto header-brand" href="<?=u("")?>">
            <img src="<?=assetsUrl()?>static/knzklive_logo.png" height="28"/>
        </a>
        <div class="btn-group col-3 d-md-none header-toggler">
            <button type="button" class="btn btn-outline-secondary rounded header-toggler-btn" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                <?php if ($my = getMe()) : ?>
                    <div class="dropdown-item">
                        <img src="<?=$my["misc"]["avatar"]?>" class="avatar_img_navbar rounded"/>
                        <a href="<?=u("settings")?>"><span class="badge badge-info"><b class="now_user_point"><?=$my["point_count"]?></b>KP</span></a>
                    </div>
                        <?php if ($my["broadcaster_id"]) : ?>
                            <a class="dropdown-item bg-warning" href="<?=u("new")?>"><b>配信を<?=$my["live_current_id"] ? "管理" : "始める"?></b></a>
                        <?php endif; ?>
                    <a class="dropdown-item" href="<?=u("settings")?>">ユーザー設定</a>
                    <a class="dropdown-item" href="<?=u("logout")?>">ログアウト</a>
                <?php else : ?>
                    <h6 class="dropdown-header">次のアカウントでログイン:</h6>
                    <button class="dropdown-item text-primary" data-toggle="modal" data-target="#loginModal"><i class="fab fa-mastodon"></i> Mastodon</button>
                    <a class="dropdown-item text-info" href="<?=u("auth/twitter")?>"><i class="fab fa-twitter"></i> Twitter</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-none d-md-flex align-items-center justify-content-end row col-6">
            <?php if ($my = getMe()) : ?>
                <div class="header-items">
                    <a href="<?=u("settings")?>"><span class="badge badge-info"><b class="now_user_point"><?=$my["point_count"]?></b>KP</span></a>
                </div>
                    <?php if ($my["broadcaster_id"]) : ?>
                            <div class="header-items">
                                <a class="btn btn-outline-warning" href="<?=u("new")?>"><b>配信を<?=$my["live_current_id"] ? "管理" : "始める"?></b></a>
                            </div>
                    <?php endif; ?>
                    <div class="nav-item dropdown active header-items">
                        <a class="nav-link header_avatar_dropdown" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src="<?=$my["misc"]["avatar"]?>" class="avatar_img_navbar rounded"/>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="<?=(empty($my["broadcaster_id"]) ? "#" : userUrl($my["broadcaster_id"]))?>">
                                <b><?=$my["name"]?></b><br>
                                <small class="text-secondary"><?=$my["acct"]?></small>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?=u("settings")?>">ユーザー設定</a>
                            <a class="dropdown-item" href="<?=u("logout")?>">ログアウト</a>
                        </div>
                    </div>
            <?php else : ?>
                <form class="header-items">
                    Login:
                    <button type="button" class="btn btn-outline-primary ml-2" data-toggle="modal" data-target="#loginModal"><b>Mastodon</b></button>
                    <a class="btn btn-outline-info ml-2" href="<?=u("auth/twitter")?>"><b>Twitter</b></a>
                </form>
            <?php endif; ?>
        </div>
    </div>
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
                    <input type="text" class="form-control" placeholder="ex) <?=$env["masto_login"]["domain"]?>" id="login_domain" list="domain_list">
                </div>
                <small>
                    <?=$env["masto_login"]["domain"]?>以外のアカウントでログインすると一部のアカウントではコメントが表示できない可能性があります。
                    <a href="https://knzklive-docs.knzk.me/#/docs/user/faq.md#外部インスタンスから投稿したコメントトゥートが表示できない" target="_blank">理由</a>
                </small><br>
                <small>KnzkLiveではアクセストークンをデータベースに保管しません。また、認証とコメント以外に使用する事はありません。</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-block" onclick="login()">ログイン</button>
            </div>
        </div>
    </div>
</div>
<datalist id="domain_list">
    <option value="knzk.me"></option>
    <option value="mastodon.social"></option>
    <option value="mstdn.jp"></option>
    <option value="friends.nico"></option>
    <option value="pawoo.net"></option>
</datalist>
<script>
    const domain = localStorage.getItem("knzklive_domain_last");
    if (domain) document.getElementById("login_domain").value = domain;

    function login() {
        const login_domain = document.getElementById('login_domain').value;
        localStorage.setItem("knzklive_domain_last", login_domain);
        location.href=`<?=u('login')?>?domain=` + login_domain;
    }
</script>
