<?php
require_once("../lib/bootloader.php");
$my = getMe();
if (!$my) {
  http_response_code(403);
  exit("ERR:ログインしてください。");
}
$plog = get_point_log($my["id"]);

if ($my["broadcaster_id"]) {
  $live_stat_times = getAllLiveTime($my["id"]);
}

if ($_POST) {
  $my["misc"]["live_toot"] = isset($_POST["live_toot"]);
  $my["misc"]["to_title"] = isset($_POST["to_title"]);
  $my["misc"]["no_toot_default"] = isset($_POST["no_toot_default"]);
  $my["misc"]["auto_close"] = isset($_POST["auto_close"]);
  $my["misc"]["auto_open_start"] = isset($_POST["auto_open_start"]);
  $my["misc"]["hide_watching_list"] = isset($_POST["hide_watching_list"]);
  $my["misc"]["webhook_url"] = $_POST["webhook_url"];
  $my["misc"]["donate_url"] = $_POST["donate_url"];
  setConfig($my["id"], $my["misc"]);

  if ($_POST["broadcaster_id"] !== $my["broadcaster_id"] && !empty($my["broadcaster_id"])) {
    if (!updateBroadcasterId($my["id"], $_POST["broadcaster_id"])) exit("ERR: この配信者IDは使用できません。");
    $userCache = null;
    $my = getMe();
  }
}
?>
<!doctype html>
<html lang="ja">
<head>
  <?php include "../include/header.php"; ?>
  <title>ユーザー設定 - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../include/navbar.php"; ?>
<form method="post">
  <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
  <div class="container">
    <div class="box">
      <h4>プロフィール設定</h4>
      Mastodonで変更した後、KnzkLiveでログアウト→ログインすると更新されます。
    </div>
    <div class="box">
      <h4>視聴設定</h4>
      <div class="form-group">
        <div class="custom-control custom-checkbox">
          <input type="checkbox" class="custom-control-input" id="no_toot" name="no_toot_default" value="1" <?=(!empty($my["misc"]["no_toot_default"]) ? "checked" : "")?>>
          <label class="custom-control-label" for="no_toot">
            「コメントのみ投稿」をデフォルトにする
          </label>
        </div>
      </div>

      <div class="form-group">
        <div class="custom-control custom-checkbox">
          <input type="checkbox" class="custom-control-input" id="hide_watching_list" name="hide_watching_list" value="1" <?=(!empty($my["misc"]["hide_watching_list"]) ? "checked" : "")?>>
          <label class="custom-control-label" for="hide_watching_list">
            こっそり視聴モードを有効にする<br>
            <small>通常、配信者はログイン中の視聴ユーザー一覧を閲覧できますが、これを有効にするとあなたは表示されなくなります。</small>
          </label>
        </div>
      </div>
    </div>
    <?php if ($my["broadcaster_id"]) : ?>
      <div class="box">
        <h4>配信者設定</h4>
        <p>* この設定は過去、未来全ての配信に適用されます。</p>

        <ul>
          <li><a href="<?=u("live_manage_ngword")?>">NGワード設定</a></li>
          <li><a href="<?=u("live_manage_blocking")?>">ユーザーブロック設定</a></li>
        </ul>

        <div class="form-group">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="conf_to_title" name="to_title" value="1" <?=(!empty($my["misc"]["to_title"]) ? "checked" : "")?>>
            <label class="custom-control-label" for="conf_to_title">
              配信枠取得の際に前回のタイトルと説明、ハッシュタグを予め記入する
            </label>
          </div>
        </div>

        <div class="form-group">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="conf_auto_close" name="auto_close" value="1" <?=(!empty($my["misc"]["auto_close"]) ? "checked" : "")?>>
            <label class="custom-control-label" for="conf_auto_close">
              配信クライアント(OBS等)の配信終了を検知したら自動で枠を閉じる
            </label>
          </div>
        </div>

        <div class="form-group">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="conf_auto_open_start" name="auto_open_start" value="1" <?=(!empty($my["misc"]["auto_open_start"]) ? "checked" : "")?>>
            <label class="custom-control-label" for="conf_auto_open_start">
              配信開始時に自動で配信画面を新規タブで開く
            </label>
          </div>
        </div>

        <div class="form-group">
          <div class="form-group">
            <label for="conf_webhook_url">WebHook URL</label>
            <input type="url" class="form-control" id="conf_webhook_url" name="webhook_url" aria-describedby="conf_webhook_url_note" placeholder="https://hogehoge.example/api"
                   value="<?=isset($my["misc"]["webhook_url"]) ? s($my["misc"]["webhook_url"]) : ""?>">
            <small id="conf_webhook_url_note" class="form-text text-muted">配信開始時に呼び出されます。</small>
          </div>
        </div>

        <div class="form-group">
          <div class="form-group">
            <label for="conf_webhook_url">支援リンク</label>
            <input type="url" class="form-control" id="conf_donate_url" name="donate_url" aria-describedby="conf_donate_url_note" placeholder="https://example.com/hogehoge"
                   value="<?=isset($my["misc"]["donate_url"]) ? s($my["misc"]["donate_url"]) : ""?>">
            <small id="conf_donate_url_note" class="form-text text-muted">FANBOXやfantiaなどの支援リンクを配信ページの下部に追加できます。</small>
          </div>
        </div>

        <div class="form-group">
          <div class="form-group">
            <label for="conf_webhook_url">配信者ID</label>
            <input type="text" class="form-control" name="broadcaster_id" value="<?=isset($my["broadcaster_id"]) ? s($my["broadcaster_id"]) : ""?>" required>
            <small class="form-text text-muted">
              <a href="<?=userUrl($my["broadcaster_id"])?>" target="_blank">ユーザーページ</a>のURLのID(/user/~~)を変更できます。英数字、アットマーク、ピリオドが使用できます。<br>
              <span class="text-danger">変更すると以前のURLは使用できなくなりますのでご注意ください。</span>
            </small>
          </div>
        </div>
      </div>
    <?php else : ?>
      <div class="box">
        <h3>配信を始める</h3>
        <div class="alert alert-warning" role="alert">
          配信権限が必要です。
        </div>
      </div>
    <?php endif; ?>
    <button type="submit" class="btn btn-primary btn-lg">更新</button>
    <hr>

    <?php if ($my["broadcaster_id"]) : ?>
      <?php $hash = (empty($my["opener_token"]) || isset($_GET["openertoken"])) ? generateOpenerToken($my["id"]) : $my["opener_token"]; ?>
      <div class="box" id="opener-token">
        <h4>Openerトークン</h4>
        <a href="https://github.com/KnzkDev/KnzkLiveOBSOpener" target="_blank">KnzkLiveOBSOpenerについて</a>
        <div class="col-md-5 mt-2 mb-2">
          <div class="input-group">
            <input type="text" class="form-control" aria-describedby="openertoken-bt" readonly placeholder="クリックで表示" onclick="window.prompt('Openerトークン', '<?=$hash?>')">
            <div class="input-group-append">
              <button class="btn btn-outline-danger" type="button" id="openertoken-bt" onclick="location.href = '?openertoken=regen'">再生成</button>
            </div>
          </div>
        </div>
      </div>

      <h4 class="mt-4">あなたの配信者ステータス</h4>
      <small>配信終了時に更新されます</small><br>
      累積配信時間: <?=dispSecDate(array_sum($live_stat_times))?> · 最高配信時間: <?=dispSecDate($live_stat_times[0])?> · 平均配信時間: <?=dispSecDate(array_sum($live_stat_times) / count($live_stat_times))?><br>
      累積視聴者数: <?=$my["misc"]["viewers_max"]?>人 · 最高同時視聴者数: <?=$my["misc"]["viewers_max_concurrent"]?>人<br>
      累積コメント数: <?=$my["misc"]["comment_count_all"]?>コメ · 最高コメント数: <?=$my["misc"]["comment_count_max"]?>コメ<br>
      累積ポイント取得数: <?=$my["misc"]["point_count_all"]?>KP · 最高ポイント取得数: <?=$my["misc"]["point_count_max"]?>KP
      <hr>
    <?php endif; ?>
    <div class="box">
      <h4>KnzkPoint</h4>
      神崎ポイントを貯めると、配信のアイテムと交換したり、ユーザー間でプレゼントしたりできます。<br>
      <?php if ($my["point_count"] > 0) : ?>
      <a href="<?=u("knzkpoint/new")?>" class="badge badge-info">チケットを発行</a> · <a href="<?=u("knzkpoint/present")?>" class="badge badge-info">KPをプレゼント</a> ·
      <?php endif; ?>
      <a href="<?=u("ticket")?>" class="badge badge-info">チケットを使用</a><br><br>
      <p>
        <b>現在の保有ポイント: <span class="badge badge-success"><?=$my["point_count"]?>KP</span></b>
      </p>
      <h5>あなたの獲得した統計</h5>
      <div class="table-responsive">
        <table class="table">
          <thead>
          <tr>
            <th></th>
            <th>今日</th>
            <th>昨日</th>
            <th>今月</th>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td><a href="#" onclick="alert('公開トゥート/配信でコメントすると、1投稿あたり2KPゲットできます。（1日500KPまで）\n獲得したポイントは次の日から使用できます。\nトゥートは「公開」に設定されていて、なおかつリプライでないものが対象です。ワーカーの状態によって取りこぼす場合があります。');return false">トゥート/コメント</a></td>
            <td><?=($my["point_count_today_toot"] > 500 ? 500 : s($my["point_count_today_toot"]))?> <small>(予定)</small></td>
            <td><?=get_point_log_stat($my["id"], "toot", "today")?></td>
            <td><?=get_point_log_stat($my["id"], "toot", "month")?></td>
          </tr>
          <tr>
            <td><a href="#" onclick="alert('他のユーザーによって作成されたチケットを残高に追加したり、プレゼントしてもらう事ができます。');return false">チケット/プレゼント</a></td>
            <td><?=get_point_log_stat($my["id"], "user", "today")?></td>
            <td><?=get_point_log_stat($my["id"], "user", "yesterday")?></td>
            <td><?=get_point_log_stat($my["id"], "user", "month")?></td>
          </tr>
          <tr>
            <td><a href="#" onclick="alert('配信をすると配信でリスナーから送られたアイテムのポイントの一定割合が貰えます。');return false">配信</a></td>
            <td><?=get_point_log_stat($my["id"], "live", "today")?></td>
            <td><?=get_point_log_stat($my["id"], "live", "yesterday")?></td>
            <td><?=get_point_log_stat($my["id"], "live", "month")?></td>
          </tr>
          <tr>
            <td>その他</td>
            <td><?=get_point_log_stat($my["id"], "other", "today")?></td>
            <td><?=get_point_log_stat($my["id"], "other", "yesterday")?></td>
            <td><?=get_point_log_stat($my["id"], "other", "month")?></td>
          </tr>
          </tbody>
        </table>

        <h5>獲得・使用履歴</h5>
        <table class="table">
          <thead>
          <tr>
            <th>日時</th>
            <th>増減</th>
            <th>タイプ</th>
            <th>詳細</th>
          </tr>
          </thead>
          <tbody>
          <?php foreach ($plog as $item) :
            if ($item["type"] === "toot") $item["type"] = "トゥート/コメント";
            elseif ($item["type"] === "user") $item["type"] = "チケット/プレゼント";
            elseif ($item["type"] === "live") $item["type"] = "配信";
            else $item["type"] = "その他";
            ?>
            <tr>
              <td><?=s($item["created_at"])?></td>
              <td><?=s($item["point"])?></td>
              <td><?=s($item["type"])?></td>
              <td><?=s($item["data"])?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</form>

<?php include "../include/footer.php"; ?>
<script id="item_emoji_tmpl" type="text/x-handlebars-template">
  <div class="item_emoji {{class}}" style="{{style}}" id="{{random_id}}">
    {{repeat_helper}}
  </div>
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.12/handlebars.min.js" integrity="sha256-qlku5J3WO/ehJpgXYoJWC2px3+bZquKChi4oIWrAKoI=" crossorigin="anonymous"></script>
</body>
</html>
