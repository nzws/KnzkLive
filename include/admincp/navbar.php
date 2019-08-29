<div class="col-sm-3">
    <ul class="list-group list-group-flush">
        <li class="list-group-item text-center brand">
            <img src="<?=assetsUrl()?>static/knzklive_logo.png" class="mb-2 mt-2"/>
            <div><b>KnzkLive - AdminCP</b></div>
        </li>
        <a href="<?=u()?>" class="list-group-item list-group-item-action"><?=i('chevron-left')?> Back to KnzkLive</a>
        <a href="<?=u('admincp/index')?>" class="list-group-item list-group-item-action"><?=i('tachometer-alt')?> Dashboard</a>
        <a href="<?=u('admincp/streams')?>" class="list-group-item list-group-item-action"><?=i('broadcast-tower')?> Streams</a>
        <a href="<?=u('admincp/stream_servers')?>" class="list-group-item list-group-item-action disabled"><?=i('server')?> Stream Servers</a>
        <a href="<?=u('admincp/accounts')?>" class="list-group-item list-group-item-action"><?=i('users')?> Accounts</a>
        <a href="<?=u('admincp/uploads')?>" class="list-group-item list-group-item-action"><?=i('cloud-upload-alt')?> Uploads</a>
        <a href="<?=u('admincp/point_log')?>" class="list-group-item list-group-item-action"><?=i('history')?> Point Log</a>
    </ul>
</div>
