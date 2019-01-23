<?php
require_once("../lib/bootloader.php");
$my = getMe();
if (!$my) {
  http_response_code(403);
  exit("ERR:ログインしてください。");
}
$plog = get_point_log($my["id"]);

if ($_POST) {
  $my["misc"]["live_toot"] = !!$_POST["live_toot"];
  $my["misc"]["to_title"] = !!$_POST["to_title"];
  $my["misc"]["no_toot_default"] = !!$_POST["no_toot_default"];
  $my["misc"]["webhook_url"] = $_POST["webhook_url"];
  setConfig($my["id"], $my["misc"]);
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
      <h4>コメント設定</h4>
      <div class="form-group">
        <div class="custom-control custom-checkbox">
          <input type="checkbox" class="custom-control-input" id="no_toot" name="no_toot_default" value="1" <?=($my["misc"]["no_toot_default"] ? "checked" : "")?>>
          <label class="custom-control-label" for="no_toot">
            ローカル投稿をデフォルトにする
          </label>
        </div>
      </div>
    </div>
    <?php if ($my["is_broadcaster"]) : ?>
      <div class="box">
        <h4>配信者設定</h4>
        <p>* この設定は過去、未来全ての配信に適用されます。</p>

        <div class="form-group">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="conf_to_title" name="to_title" value="1" <?=($my["misc"]["to_title"] ? "checked" : "")?>>
            <label class="custom-control-label" for="conf_to_title">
              配信枠取得の際に前回のタイトルと説明を予め記入する
            </label>
          </div>
        </div>

        <div class="form-group">
          <div class="form-group">
            <label for="conf_webhook_url">WebHook URL</label>
            <input type="url" class="form-control" id="conf_webhook_url" name="webhook_url" aria-describedby="conf_webhook_url_note" placeholder="https://hogehoge.example/api" value="<?=$my["misc"]["webhook_url"]?>">
            <small id="conf_webhook_url_note" class="form-text text-muted">配信開始時に呼び出されます。</small>
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
    <button type="submit" class="btn btn-primary">更新</button>
    <hr>
    <div class="box">
      <h4>KnzkPoint</h4>
      神崎ポイントを貯めると、配信のアイテムと交換したり、ユーザー間でプレゼントしたりできます。<br>
      <a href="<?=u("knzkpoint/new")?>" class="badge badge-info">チケットを発行</a> · <a href="<?=u("ticket")?>" class="badge badge-info">チケットを使用</a> · <a href="<?=u("knzkpoint/present")?>" class="badge badge-info">KPをプレゼント</a><br><br>
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
</body>
</html>
