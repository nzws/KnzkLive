<div class="card admin_panel">
  <div class="card-header bg-danger text-white">
    <b>サービスモデレータ:</b> 管理パネル
  </div>
  <div class="card-body">
    <p>
      * Discordにログが残ります
    </p>
    <button class="btn btn-danger" onclick="live.admin.toggle('stop', true)"><i class="far fa-stop-circle"></i> 配信の強制終了</button>
    <button type="button" class="btn admin-toggle btn-warning" onclick="live.admin.toggle('sensitive', true)"><i class="fas fa-eye-slash"></i> センシティブを強制的に有効化</button>
    <button type="button" class="btn admin-toggle btn-warning" onclick="live.admin.toggle('item', true)"><i class="fas fa-hat-wizard"></i> アイテムを強制的に無効化</button>
    <button type="button" class="btn admin-toggle btn-warning" onclick="live.admin.toggle('comment', true)"><i class="fas fa-comment-slash"></i> コメントを強制的に無効化</button>
  </div>
</div>
