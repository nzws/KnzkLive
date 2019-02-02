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
                onclick="create_blocking()"
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

  function create_blocking() {
    const acct = elemId("blocking_acct").value;
    if (confirm(`「${acct}」をブロックします。\nよろしいですか？`)) {
      fetch('<?=u("api/client/ngs/manage_users")?>', {
        headers: {'content-type': 'application/x-www-form-urlencoded'},
        method: 'POST',
        credentials: 'include',
        body: buildQuery({
          csrf_token: `<?=$_SESSION['csrf_token']?>`,
          type: 'add',
          acct: acct,
          is_permanent: elemId("blocking_permanent").checked ? 1 : 0,
          is_blocking_watch: elemId("blocking_blocking_watch").checked ? 1 : 0
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
          elemId("blocking_acct").value = "";
          $("#blockingModal").modal("hide");
          <?php if (empty($live)) : ?>
          location.reload();
          <?php endif; ?>
        } else {
          alert("エラーが発生しました。データベースに問題が発生している可能性があります。");
        }
      }).catch(function(error) {
        console.error(error);
        alert("内部エラーが発生しました");
      });
    }
  }

  function open_blocking_modal(acct) {
    elemId("blocking_acct").value = acct;
    $("#blockingModal").modal("show");
  }
</script>
