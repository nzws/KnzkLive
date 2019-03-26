<div class="card admin_panel wide_hide">
    <div class="card-header bg-primary text-white">
        <b>コラボレータパネル</b>
    </div>
    <div class="card-body">
        <button class="btn btn-success" data-toggle="modal" data-target="#startCollaboModal"><?=i("power-off")?> 配信に参加</button>
        <button type="button" class="btn admin-toggle btn-<?=($live["misc"]["able_comment"] ? "warning on" : "info off")?>" onclick="live.admin.toggle('comment')" id="admin_panel_comment_display"><i class="fas fa-comment-slash"></i> コメントを<span class="on">無効化</span><span class="off">有効化</span></button>
        <button type="button" class="btn admin-toggle btn-<?=($live["misc"]["is_sensitive"] ? "info on" : "warning off")?>" onclick="live.admin.toggle('sensitive')" id="admin_panel_sensitive_display"><i class="fas fa-eye-slash"></i> センシティブを<span class="on">無効化</span><span class="off">有効化</span></button>
        <button type="button" class="btn admin-toggle btn-<?=($live["misc"]["able_item"] ? "warning on" : "info off")?>" onclick="live.admin.toggle('item')" id="admin_panel_item_display"><i class="fas fa-hat-wizard"></i> アイテムを<span class="on">無効化</span><span class="off">有効化</span></button>
        <hr>
        <h5>コラボレータについて</h5>
        <b>コラボレータ</b>は、この配信内で配信者さんに招待されたユーザが<b>コメント管理</b>や<b>配信に参加</b>することのできる権限です。<br>
        詳しくは配信者さんに聞いてください。
        <p>
            <a href="<?=u("terms")?>" target="_blank">利用規約とガイドライン</a> / <a href="https://knzklive-docs.knzk.me/#/docs/streamer/collaboration.md" target="_blank">コラボレータガイド</a>
        </p>
    </div>
</div>

<div class="modal fade" id="startCollaboModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">コラボ配信に参加</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    コラボ配信に参加すると、このページで配信者さんと一緒に配信できます！配信者さんがKnzkLiveの配信権限を持っているため、あなたが配信権限を持っている必要はありません。<br>
                    進め方は<a href="https://knzklive-docs.knzk.me/#/docs/streamer/collaboration.md" target="_blank">コラボレータガイド</a>のコラボ配信の項目をお読みください。
                </p>

                <?php if (empty($live["misc"]["collabo"][$my["id"]]["slot"]) && getAbleSlot()) : ?>
                    <button class="btn btn-primary btn-block btn-bg" onclick="live.admin.getCollaboSlot()">コラボ配信に参加 <small>(配信枠を取得)</small></button>
                    <small class="text-danger">「コラボ配信に参加」をクリックした時点で、<a href="<?=u("terms")?>" target="_blank">利用規約とガイドライン</a>に同意したものとします。</small>
                <?php elseif (!empty($live["misc"]["collabo"][$my["id"]]["slot"])) : ?>
                    <div class="form-group">
                        <label>URL</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="rtmp://<?=getSlot($live["misc"]["collabo"][$my["id"]]["slot"])["server_ip"]?>/live" readonly onclick="this.select(0,this.value.length)">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>ストリームキー</label>
                        <div class="input-group">
                            <button onclick="window.prompt('ストリームキー', '<?=$live["id"]?>stream<?=$my["id"]?>collabo?token=<?=$live["misc"]["collabo"][$my["id"]]["token"]?>')" class="btn btn-secondary btn-block">クリックで表示</button>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="alert alert-danger">
                        現在、配信枠が不足しています。<br>
                        他のユーザの配信終了までお待ちください。
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
