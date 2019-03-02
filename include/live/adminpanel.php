<div class="card admin_panel">
  <div class="card-header">
    配信管理
  </div>
  <div class="card-body">
    <h5>基本設定</h5>
    <a class="btn btn-danger" onclick="return confirm('配信を終了します。よろしいですか？')" href="<?=u("live_manage")?>?mode=shutdown&t=<?=$_SESSION['csrf_token']?>"><i class="far fa-stop-circle"></i> 配信終了</a>
    <button type="button" class="btn btn-primary" onclick="live.admin.openEditLive()"><i class="fas fa-pencil-alt"></i> 編集</button>
    <button type="button" class="btn admin-toggle btn-<?=($live["misc"]["is_sensitive"] ? "info on" : "warning off")?>" onclick="live.admin.toggle('sensitive')" id="admin_panel_sensitive_display"><i class="fas fa-eye-slash"></i> センシティブを<span class="on">無効化</span><span class="off">有効化</span></button>
    <button type="button" class="btn admin-toggle btn-<?=($live["misc"]["able_item"] ? "warning on" : "info off")?>" onclick="live.admin.toggle('item')" id="admin_panel_item_display"><i class="fas fa-hat-wizard"></i> アイテムを<span class="on">無効化</span><span class="off">有効化</span></button>
    <hr>
    <h5>コメント管理</h5>
    <button type="button" class="btn admin-toggle btn-<?=($live["misc"]["able_comment"] ? "warning on" : "info off")?>" onclick="live.admin.toggle('comment')" id="admin_panel_comment_display"><i class="fas fa-comment-slash"></i> コメントを<span class="on">無効化</span><span class="off">有効化</span></button>
    <a class="btn btn-primary" href="<?=u("live_manage_ngword")?>" target="_blank"><i class="fas fa-comment-slash"></i> NGワード管理</a>
    <a class="btn btn-primary" href="<?=u("live_manage_blocking")?>" target="_blank"><i class="fas fa-user-slash"></i> ブロックユーザ管理</a>
    <!--
    <button type="button" class="btn btn-info" onclick="openEditLive()"><i class="fas fa-user-shield"></i> モデレータ管理</button>
    -->
    <hr>
    <h5>ツール</h5>
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#enqueteModal" id="open_enquete_btn"><i class="fas fa-poll-h"></i> アンケート</button>
    <button type="button" class="btn btn-warning" onclick="live.vote.close()" id="close_enquete_btn" style="display: none"><i class="fas fa-poll-h"></i> アンケートを終了</button>

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addChModal"><i class="fas fa-donate"></i> CH追加</button>
    <hr>
    <h5>ログ</h5>
    <!--<button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modlogModal"><i class="fas fa-gavel"></i> モデレーションログ</button>-->
    <button type="button" class="btn btn-secondary" onclick="live.admin.openListenerModal()"><i class="fas fa-users"></i> リスナー一覧</button>
  </div>
</div>
