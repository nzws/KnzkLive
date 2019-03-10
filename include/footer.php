<footer class="footer mt-4 py-3">
  <div class="container">
    <div class="row">
      <div class="col-md-4">
        <h5>KnzkLive</h5>
        <ul>
          <li><a href="<?=u()?>">ホーム</a></li>
          <li><a href="<?=u("help")?>">ヘルプ</a></li>
          <li><a href="<?=u("terms")?>">利用規約とガイドライン</a></li>
          <li><a href="https://knzk.me/@KnzkLiveNotification" target="_blank">プッシュ通知を有効化</a></li>
          <li><a href="https://knzk.me/@TIPKnzk" target="_blank">@TIPKnzk</a></li>
          <li><a href="https://nzws.me/donate.html" target="_blank">KnzkLiveを支援</a></li>
          <?php if (empty($my)) : ?>
            <li><a href="#" data-toggle="modal" data-target="#loginModal">Mastodonでログイン</a></li>
            <li><a href="<?=u("auth/twitter")?>">Twitterでログイン</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="col-md-4">
        <h5>Knzk.me</h5>
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
          <li><a href="https://github.com/KnzkDev" target="_blank">GitHub Organization</a></li>
          <li><a href="https://knzk.app/" target="_blank">KnzkApp</a></li>
          <li><a href="https://023.jp/" target="_blank">023.jp</a></li>
          <li><a href="https://rip.knzk.me/" target="_blank">rip.knzk.me</a></li>
        </ul>
      </div>
    </div>
    <span class="text-muted">最新のGoogle Chrome / Windows環境で動作確認済みです。</span><br>
    <span class="text-muted"><a href="https://github.com/yuzulabo/KnzkLive" target="_blank">KnzkLive Project</a> made with ♥ by <a href="https://github.com/KnzkDev" target="_blank">KnzkDev Team</a></span>
  </div>
</footer>
