<?php require_once "../../lib/bootloader.php"; ?>
<!doctype html>
<html lang="ja" data-page="knzkpay-index">
<head>
    <?php include "../../include/header.php"; ?>
    <title>KnzkPay - <?=$env["Title"]?></title>
</head>
<body>

<div class="top" style="background-image: url('https://media.knzk.me/media_attachments/files/004/542/172/original/18744b9c925159c6.png')">
    <div class="hero text-center">
        <img src="<?=assetsUrl()?>static/knzkpay/logo.png" width="400" class="logo"/>

        <p class="h2 text">
        <b>KnzkPay</b>で<br>
        <b>KnzkPoint</b>をカンタンやり取り。<br>
        </p>

        <p class="button">
        <a href="#getting-started" class="btn btn-info btn-lg">今すぐはじめる</a>
        </p>

        <img src="<?=assetsUrl()?>static/knzkpay/top.png" class="screenshot"/>
    </div>
</div>

<div class="page text-center">
    <h1>QRコードで簡単</h1>
    <p class="h4">
    <span class="text-warning">受け取る人</span>がQRコードを生成して、<br>
    <span class="text-info">送る人</span>がリーダで読み取るだけ。
    </p>
    <div class="row justify-content-center mt-4" style="width: 100%;">
        <div class="col-md-3 col-11 mt-2 guide border border-secondary">
            <h4 class="text-warning">受け取る人</h4>
            <p>
                金額を入力して、<br>
                <span class="text-info">送る人</span>に生成されたQRコードを見せる。
            </p>
            <img src="<?=assetsUrl()?>static/knzkpay/receiver.png" class="screenshot"/>
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-3 col-11 mt-2 guide border border-secondary">
            <h4 class="text-info">送る人</h4>
            <p>
                <span class="text-warning">受け取る人</span>のQRコードを読み取って、<br>
                <b>支払う</b> をクリックする。
            </p>
            <img src="<?=assetsUrl()?>static/knzkpay/sender.png" class="screenshot"/>
        </div>
    </div>
</div>

<div class="page text-center" style="background: #303F6B">
    <h1>手数料無料</h1>
    <p class="h4">
    KnzkPay を使用した KnzkPoint 送信を使用すれば、<br>
    今なら手数料完全無料。
    </p>
    <div class="h1 mt-4">
        <?=i("money-bill-wave")?> Fee <s>15%</s> → <b>0%</b>
    </div>
</div>

<div class="page text-center" id="getting-started">
    <h1>今すぐはじめよう</h1>
    <p class="h4">
        アプリ不要でブラウザとKnzkLiveアカウントがあればすぐに始められます。
    </p>

    <button class="btn btn-info btn-lg mt-4">Comming Soon!</button>

    <p class="text-muted mt-4">
        * 需要があれば作るかもしれない...?
    </p>
</div>

<?php include "../../include/footer.php"; ?>
</body>
</html>
