<div class="modal fade" id="shareModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">共有</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row justify-content-md-center share_buttons">
          <button class="btn btn-outline-info col-md-2" onclick="share_modal('twitter')">
            <i class="fab fa-twitter fa-fw fa-2x"></i><br>
            Twitter
          </button>
          <button class="btn btn-outline-primary col-md-2" onclick="share_modal('mastodon')">
            <i class="fab fa-mastodon fa-fw fa-2x"></i><br>
            Mastodon
          </button>
          <button class="btn btn-outline-danger col-md-2" onclick="share_modal('weibo')">
            <i class="fab fa-weibo fa-fw fa-2x"></i><br>
            Weibo
          </button>
          <button class="btn btn-outline-primary col-md-2" onclick="share_modal('facebook')">
            <i class="fab fa-facebook fa-fw fa-2x"></i><br>
            Facebook
          </button>
          <button class="btn btn-outline-success col-md-2" onclick="share_modal('line')">
            <i class="fab fa-line fa-fw fa-2x"></i><br>
            LINE
          </button>
          <button class="btn btn-outline-info col-md-2" onclick="share_modal('skype')">
            <i class="fab fa-skype fa-fw fa-2x"></i><br>
            Skype
          </button>
          <button class="btn btn-outline-danger col-md-2" onclick="share_modal('flipboard')">
            <i class="fab fa-flipboard fa-fw fa-2x"></i><br>
            Flipboard
          </button>
        </div>
        <div class="row" style="margin-top: 10px">
          <div class="col-md-12">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text" id="share_url">URL</span>
              </div>
              <input type="text" class="form-control" aria-describedby="share_url" readonly value="<?=$liveurl?>" onclick="this.select(0,this.value.length)">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-hat-wizard"></i> アイテム <span class="badge badge-info"><b class="now_user_point"><?=$my["point_count"]?></b>KP</span></h5>

        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h5>絵文字</h5>
        <div class="row">
          <div class="col-sm-4">
            絵文字:
            <select class="form-control" id="item_emoji_emoji">
              <option>👍</option>
              <option>❤️</option>
              <option>👏️</option>
              <option>🎉️</option>
              <option>🍮</option>
              <option value="liver">配信者のアイコン</option>
              <option value="me">あなたのアイコン</option>
            </select>
          </div>
          <div class="col-sm-4">
            方向:
            <select class="form-control" id="item_emoji_dir">
              <option value="left-to-right">左から右</option>
              <option value="right-to-left">右から左</option>
              <option value="top-to-bottom">上から下</option>
              <option value="bottom-to-top">下から上</option>
            </select>
          </div>
          <div class="col-sm-4">
            個数 <small>(1~100, <b>n*5</b>KP)</small>:
            <input type="number" class="form-control" id="item_emoji_count" value="1" min="1" max="100" onkeyup="update_money_disp('emoji')" onchange="update_money_disp('emoji')">
          </div>
        </div>
        <div class="mt-2">
          <div class="float-left">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="item_emoji_spin" onchange="update_money_disp('emoji')">
              <label class="custom-control-label" for="item_emoji_spin">
                回転あり (+<b>30</b>KP)<br>
              </label>
            </div>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="item_emoji_big" onchange="update_money_disp('emoji')">
              <label class="custom-control-label" for="item_emoji_big">
                大きさ二倍盛り (+<b>30</b>KP)<br>
              </label>
            </div>
          </div>
          <div class="text-right">
            <button class="btn btn-success" onclick="item_buy('emoji')"><span id="item_emoji_point">5</span>KPで投下</button>
          </div>
        </div>
        <hr>
        <?php if ($liveUser["id"] === 2 || $liveUser["id"] === 84 || $env["is_testing"]) : ?>
          <h5>神崎コンギョ (音)</h5>
          コ　ン　ギ　ョ
          <div class="text-right">
            <button class="btn btn-success" onclick="item_buy('knzk_kongyo')">1000KPで投下</button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="sensitiveModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">警告！</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        この先、配信はセンシティブな内容を含む可能性があります。続行しますか？
        <button type="button" class="btn btn-danger btn-lg btn-block" data-dismiss="modal" onclick="document.getElementById('iframe').src = frame_url">:: 視聴する ::</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="chModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-donate"></i> 支援 (コメントハイライト)</h5>

        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>
          この配信者は<a href="https://github.com/KnzkDev/KnzkLive/wiki/listener_ch" target="_blank"><b>コメントハイライト</b></a>機能を有効にしているため、下記の手順で支援すると、あなたがコメント欄で目立つように表示させる事が出来ます。<br>
        </p>
        <?php if (!empty($liveUser["donation_desc"])) : ?>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">ID</span>
            </div>
            <input type="text" class="form-control" readonly value="<?=$my["acct"]?>" onclick="this.select(0,this.value.length)">
          </div>
        <hr>
        <p><?=HTMLHelper($liveUser["donation_desc"])?></p>
        <?php else : ?>
        <p>
          1. 支援ページを開いてください。
          <a href="https://www.donationalerts.com/r/<?=s($liveUser["misc"]["donation_alerts_name"])?>" target="_blank" class="btn btn-primary btn-block">支援ページを開く</a>
        </p>

        <p>
          2. <b>(重要)</b> フォームの「Your name」欄に下記のIDをコピペしてください。KnzkLiveで個人を識別するために必要です。
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text">ID</span>
          </div>
          <input type="text" class="form-control" readonly value="<?=$my["acct"]?>" onclick="this.select(0,this.value.length)">
        </div>
        </p>
        <p>
          3. その他項目も設定し、「Donate」ボタンを押して決済すると支援完了です。
        </p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($my["id"] === $live["user_id"]) : ?>
<div class="modal fade" id="enqueteModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">アンケートを新規作成</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <input type="text" class="form-control" id="open_vote_title" placeholder="投票タイトル">
        </div>
        <hr>
        <?php for ($i = 1; $i < 5; $i++) : ?>
          <div class="form-group">
            <input type="text" class="form-control" id="open_vote<?=$i?>" placeholder="内容<?=$i?>">
          </div>
        <?php endfor; ?>

        <div class="form-group">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="vote_ispost" value="1"  <?=($my["misc"]["no_toot_default"] ? "checked" : "")?>>
            <label class="custom-control-label" for="vote_ispost">
              Mastodonに投票内容を投稿しない
            </label>
          </div>
        </div>

        <small class="form-text text-muted">3と4はオプション</small>

        <button type="submit"
                onclick="open_enquete()"
                class="btn btn-success btn-block">
          :: 投票を作成 ::
        </button>
      </div>
    </div>
  </div>
</div>

  <div class="modal fade" id="addChModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">CH追加</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>
            コメントハイライトのユーザーを手動で追加できます。
          </p>
          <div class="form-group">
            <label for="blocking_acct">ユーザID</label>
            <input type="email" class="form-control" id="addch_acct" placeholder="ex) knzk@knzk.me">
          </div>

          <div class="form-group">
            <label>金額</label>
            <div class="input-group">
              <input type="number" class="form-control" id="addch_amount">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" id="addch_currency">JPY</button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="#" onclick="change_addch_currency('JPY')">JPY</a>
                  <a class="dropdown-item" href="#" onclick="change_addch_currency('USD')">USD</a>
                  <a class="dropdown-item" href="#" onclick="change_addch_currency('RUB')">RUB</a>
                  <a class="dropdown-item" href="#" onclick="change_addch_currency('EUR')">EUR</a>
                </div>
              </div>
            </div>
          </div>

          <button type="submit" onclick="add_ch()" class="btn btn-success btn-block">追加</button>
        </div>
      </div>
    </div>
  </div>
<script>
  function change_addch_currency(currency) {
    document.getElementById("addch_currency").innerText = currency;
    return false;
  }
  
  function add_ch() {
    const currency = document.getElementById("addch_currency").innerText;
    const acct = document.getElementById("addch_acct");
    const amount = document.getElementById("addch_amount");

    if (confirm(`「${acct.value}」を追加します。\nよろしいですか？`)) {
      fetch('<?=u("api/client/live/add_ch")?>', {
        headers: {'content-type': 'application/x-www-form-urlencoded'},
        method: 'POST',
        credentials: 'include',
        body: buildQuery({
          csrf_token: `<?=$_SESSION['csrf_token']?>`,
          acct: acct.value,
          amount: amount.value,
          currency: currency
        })
      }).then(function(response) {
        if (response.ok) {
          return response.json();
        } else {
          throw response;
        }
      }).then(function(json) {
        if (json["error"]) {
          alert(json["error"]);
          return null;
        }
        if (json["success"]) {
          acct.value = "";
          amount.value = "";
          $("#addChModal").modal("hide");
        } else {
          alert("エラーが発生しました。データベースに問題が発生している可能性があります。");
        }
      }).catch(function(error) {
        console.error(error);
        alert("内部エラーが発生しました");
      });
    }
  }
</script>

<div class="modal fade" id="listenerModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">　
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">リスナー一覧 <span class="badge badge-info"><b class="count"><?=$live["viewers_count"]?></b> / <span class="max"><?=$live["viewers_max"]?></span></span></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>
          <small>未ログインユーザー、こっそり視聴中ユーザーは表示されません。</small>
        </p>
        <div class="table-responsive">
          <table class="table">
            <tbody id="listener_list">
            <tr><td>読み込み中...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
