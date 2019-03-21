<div class="card admin_panel wide_hide">
  <div class="card-header bg-primary text-white">
    <b>コラボレータパネル</b>
  </div>
  <div class="card-body">
    <button class="btn btn-success" onclick="live.admin.toggle('stop', true)"><?=i("power-off")?> 配信に参加</button>
    <button type="button" class="btn admin-toggle btn-<?=($live["misc"]["able_comment"] ? "warning on" : "info off")?>" onclick="live.admin.toggle('comment')" id="admin_panel_comment_display"><i class="fas fa-comment-slash"></i> コメントを<span class="on">無効化</span><span class="off">有効化</span></button>
    <button type="button" class="btn admin-toggle btn-<?=($live["misc"]["is_sensitive"] ? "info on" : "warning off")?>" onclick="live.admin.toggle('sensitive')" id="admin_panel_sensitive_display"><i class="fas fa-eye-slash"></i> センシティブを<span class="on">無効化</span><span class="off">有効化</span></button>
    <button type="button" class="btn admin-toggle btn-<?=($live["misc"]["able_item"] ? "warning on" : "info off")?>" onclick="live.admin.toggle('item')" id="admin_panel_item_display"><i class="fas fa-hat-wizard"></i> アイテムを<span class="on">無効化</span><span class="off">有効化</span></button>
    <hr>
    <h5>コラボレータについて</h5>
    <b>コラボレータ</b>は、この配信内で配信者さんに招待されたユーザが<b>コメント管理</b>や<b>配信に参加</b>することのできる権限です。<br>
    詳しくは配信者さんに聞いてください。
  </div>
</div>
