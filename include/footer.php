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
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
<script src="<?=$env["RootUrl"]?>js/knzklive.js"></script>
