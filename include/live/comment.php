<div>
  <?php if (!empty($my)) : ?>
    <div style="<?=(empty($vote) || !empty($_SESSION["prop_vote_" . $live["id"]]) ? "display:none" : "")?>" id="prop_vote">
      <div class="alert alert-info mt-3">
        <h5><i class="fas fa-poll-h"></i> <b id="vote_title"><?=(empty($vote) ? "タイトル" : $vote["title"])?></b></h5>
        <button type="button" class="btn btn-info btn-block btn-sm mt-1" id="vote1" onclick="live.vote.vote(1)"><?=(empty($vote) ? "投票1" : $vote["v1"])?></button>
        <button type="button" class="btn btn-info btn-block btn-sm mt-1" id="vote2" onclick="live.vote.vote(2)"><?=(empty($vote) ? "投票2" : $vote["v2"])?></button>
        <button type="button" class="btn btn-info btn-block btn-sm mt-1 <?=(empty($vote) || empty($vote["v3"]) ? "invisible" : "")?>" id="vote3" onclick="live.vote.vote(3)"><?=(empty($vote) ? "投票3" : $vote["v3"])?></button>
        <button type="button" class="btn btn-info btn-block btn-sm mt-1 <?=(empty($vote) || empty($vote["v4"]) ? "invisible" : "")?>" id="vote4" onclick="live.vote.vote(4)"><?=(empty($vote) ? "投票4" : $vote["v4"])?></button>
      </div>
      <hr>
    </div>
  <?php endif; ?>
  <div id="donators" class="mt-2" style="display: none"></div>
  <p class="invisible" id="err_comment">
    <span class="text-warning">
    コメントストリーミングに再接続しています...<br>
    <small>もしこの状態から変わらなければページを再読み込みしてください。</small>
    </span>
  </p>
</div>
<div id="comments" class="comment_block mt-1<?=!empty($my["misc"]["comment_classic"]) ? " disable_reverse" : null?>"></div>
