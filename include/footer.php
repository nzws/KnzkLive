<footer class="footer mt-4 py-3 wide_hide">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>KnzkLive</h5>
                <ul>
                    <li><a href="<?=u()?>">ホーム</a></li>
                    <li><a href="<?=u("terms")?>">利用規約とガイドライン</a></li>
                    <li><a href="https://knzklive-docs.knzk.me/" target="_blank">Wiki</a></li>
                    <li><a href="https://knzk.me/@KnzkLiveNotification" target="_blank">プッシュ通知を有効化 <?=i("mastodon", "b")?></a></li>
                    <li><a href="https://knzk.me/@TIPKnzk" target="_blank">@TIPKnzk <?=i("mastodon", "b")?></a></li>
                    <li><a href="https://nzws.me/donate.html" target="_blank">KnzkLiveを支援</a></li>
                    <?php if (empty($my)) : ?>
                        <li><a href="#" data-toggle="modal" data-target="#loginModal"><?=i("mastodon", "b")?> Mastodonでログイン</a></li>
                        <li><a href="<?=u("auth/twitter")?>"><?=i("twitter", "b")?> Twitterでログイン</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Knzk.me <?=i("mastodon", "b")?></h5>
                <ul>
                    <li><a href="https://knzk.me" target="_blank">ホーム</a></li>
                    <li><a href="https://knzk.me/about/more" target="_blank">インスタンスについて</a></li>
                    <li><a href="https://knzk.me/terms" target="_blank">プライバシーポリシー</a></li>
                    <li><a href="https://knzk.me/auth/sign_in" target="_blank">ログイン</a></li>
                    <li><a href="https://knzk.me/auth" target="_blank">新規登録</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>KnzkDev</h5>
                <ul>
                    <li><a href="https://github.com/KnzkDev" target="_blank">GitHub <?=i("github", "b")?></a></li>
                    <li><a href="https://knzk.app/" target="_blank">KnzkApp</a></li>
                    <li><a href="https://023.jp/" target="_blank">023.jp</a></li>
                    <li><a href="https://rip.knzk.me/" target="_blank">rip.knzk.me</a></li>
                </ul>
            </div>
        </div>
        <p class="text-muted">
            最新のGoogle Chrome / Windows環境で動作確認済みです。<br>
            Assets served by <a href="https://www.fastly.com/" target="_blank">Fastly</a>.<br>
            <a href="https://github.com/KnzkDev/KnzkLive" target="_blank">KnzkLive Project</a> made with <?=i("heart")?> by <a href="https://github.com/KnzkDev" target="_blank">KnzkDev Team</a>.
        </p>
    </div>
</footer>
