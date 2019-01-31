# 開発環境を構築 (仮)

### 1. ダウンロード

#### 依存関係
- Node.js
  - yarn があると便利 (?)
- PHP (7.xを推奨)
- MariaDB (または MySQL)
- Composer

```bash
git clone https://github.com/KnzkDev/KnzkLive
cd KnzkLive
```

### 2. 設定
`config.sample.php` を `config.php` に、    
`config.sample.json` を `config.json` にコピーしてください。

開発環境の場合、 `config.php` の `$env["is_testing"]` は `true` にしてください。

### 3. インストール
```bash
yarn install # または npm install
composer install
```

そして、 `support/sql/knzklive.sql` をデータベースに適用してください。

### 4. 起動
```bash
php -S localhost:10213 -t public/
```
で、PHPのビルトインウェブサーバーを起動できます。 http://localhost:10213 からアクセスできます。


また、Streaming APIや、TIPKnzkなどのワーカーを起動する場合は次のコマンドを使用してください。
```bash
yarn ws:start
```
