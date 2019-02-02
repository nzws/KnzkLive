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
          <div class="custom-control custom-checkbox float-left">
            <input type="checkbox" class="custom-control-input" id="item_emoji_spin" onchange="update_money_disp('emoji')">
            <label class="custom-control-label" for="item_emoji_spin">
              回転あり (+<b>50</b>KP)<br>
              <small>一部端末で表示されない可能性があります</small>
            </label>
          </div>
          <div class="text-right">
            <button class="btn btn-success" onclick="item_buy('emoji')"><span id="item_emoji_point">5</span>KPで投下</button>
          </div>
        </div>
        <hr>
        <?php if ($liveUser["id"] === 2 || $env["is_testing"]) : ?>
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
