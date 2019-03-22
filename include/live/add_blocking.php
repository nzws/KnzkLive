<div class="modal fade" id="blockingModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">新規ユーザーブロック</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="blocking_acct">ユーザID</label>
          <input type="email" class="form-control" id="blocking_acct" placeholder="ex) knzk@knzk.me">
        </div>
        <hr>

        <?php if (isset($live) && is_collabo($my["id"], $live["id"])) : ?>
        <input type="checkbox" class="invisible" id="blocking_permanent">
        <?php else : ?>
        <div class="form-group">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="blocking_permanent" value="1">
            <label class="custom-control-label" for="blocking_permanent">
              永続的に適用<br>
              <small>有効にすると、あなたの<b>全ての配信</b>でブロックします。</small>
              <?php if (empty($live)) : ?>
              <br><small>無効の場合、次回の配信が終了した際にブロック解除されます。</small>
              <?php endif; ?>
            </label>
          </div>
        </div>
        <?php endif; ?>

        <div class="form-group">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="blocking_blocking_watch" value="1" onclick="blocking_update_note(this)">
            <label class="custom-control-label" for="blocking_blocking_watch">
              視聴もブロック<br>
              <small>有効にすると、コメントやアイテムだけでなく、<b>視聴もブロック</b>します。</small>
            </label>
          </div>
        </div>

        <small class="form-text text-muted">
          ユーザーブロックをする事で、対象のユーザーIDとユーザーが使用しているIPアドレスからの<b>コメント、アイテムをブロック</b><span id="blocking_note_watch" style="display: none">し、<b>視聴を即座に停止、ブロック</b></span>します。<br>
          確定すると、即座にリスナー全員のリストがアップデートされます。<br>
          <b>くれぐれも慎重に使用してください！</b>
        </small>

        <button type="submit"
                onclick="knzk.live.admin.addBlocking(<?=isset($live) ? $live["id"] : null?>)"
                class="btn btn-danger btn-block">
          :: ブロックする ::
        </button>
      </div>
    </div>
  </div>
</div>
<script>
  function blocking_update_note(obj) {
    if (obj.checked) $("#blocking_note_watch").show();
    else $("#blocking_note_watch").hide();
  }

  function open_blocking_modal(acct) {
    document.getElementById("blocking_acct").value = acct;
    $("#blockingModal").modal("show");
  }
</script>
