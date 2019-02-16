<div>
  <?php if (!empty($my)) : ?>
    <div class="<?=(empty($vote) || !empty($_SESSION["prop_vote_" . $live["id"]]) ? "invisible" : "")?>" id="prop_vote">
      <div class="alert alert-info mt-3">
        <h5><i class="fas fa-poll-h"></i> <b id="vote_title"><?=(empty($vote) ? "タイトル" : $vote["title"])?></b></h5>
        <button type="button" class="btn btn-info btn-block btn-sm mt-1" id="vote1" onclick="vote(1)"><?=(empty($vote) ? "投票1" : $vote["v1"])?></button>
        <button type="button" class="btn btn-info btn-block btn-sm mt-1" id="vote2" onclick="vote(2)"><?=(empty($vote) ? "投票2" : $vote["v2"])?></button>
        <button type="button" class="btn btn-info btn-block btn-sm mt-1 <?=(empty($vote) || empty($vote["v3"]) ? "invisible" : "")?>" id="vote3" onclick="vote(3)"><?=(empty($vote) ? "投票3" : $vote["v3"])?></button>
        <button type="button" class="btn btn-info btn-block btn-sm mt-1 <?=(empty($vote) || empty($vote["v4"]) ? "invisible" : "")?>" id="vote4" onclick="vote(4)"><?=(empty($vote) ? "投票4" : $vote["v4"])?></button>
      </div>
      <hr>
    </div>
  <?php endif; ?>
  <div class="mt-2 mb-2 comment_block">
    #<?=liveTag($live)?>: <b id="comment_count"><?=s($live["comment_count"])?></b>コメ
  </div>
  <?php if ($my) : ?>
  <div class="comment_block">
    <div class="form-group">
      <textarea class="form-control" id="toot" rows="3" placeholder="コメント... (<?=$my["acct"]?>としてトゥート/コメント)" onkeyup="check_limit()"></textarea>
    </div>

    <div class="custom-control custom-checkbox float-left">
      <input type="checkbox" class="custom-control-input" id="no_toot" value="1" <?=($my["misc"]["no_toot_default"] ? "checked" : "")?>>
      <label class="custom-control-label" for="no_toot">
        <small>コメントのみ投稿 <a href="#" onclick="alert('有効にした状態で投稿すると、KnzkLiveにコメントしますが<?=$_SESSION["account_provider"]?>には投稿されません。');return false">？</a></small>
      </label>
    </div>
    <div style="text-align: right">
      <b id="limit"></b>  <button class="btn btn-outline-primary" onclick="post_comment()">コメント</button>
    </div>
  </div>
  <?php else : ?>
    <p>
      <span class="text-warning">* コメントを投稿するにはログインしてください。<?=(!$liveUser["misc"]["live_toot"] ? "<br><br>{$env["masto_login"]["domain"]}のアカウントにフォローされているアカウントから #".liveTag($live)." をつけてトゥートしてもコメントする事ができます。" : "")?></span>
    </p>
  <?php endif; ?>
  <div id="donators" class="mt-2" style="display: none"></div>
  <p class="invisible" id="err_comment">
    * コメントの読み込み中にエラーが発生しました。 <a href="javascript:loadComment()">再読込</a>
  </p>
</div>
<div id="comments" class="comment_block mt-1"></div>
