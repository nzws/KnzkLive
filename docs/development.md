# 開発環境を構築 (仮)

### 1. ダウンロード

#### 依存関係

- Node.js
  - yarn があると便利 (?)
- PHP (7.x を推奨)
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
yarn start
```

で、PHP のビルトインウェブサーバーを起動できます。 http://localhost:10213 からアクセスできます。

また、Streaming API や、TIPKnzk などのワーカーを起動する場合は次のコマンドを使用してください。

```bash
yarn ws:start
```

### コマンド集

| コマンド                                                   | 説明                                                                                                     |
| ---------------------------------------------------------- | -------------------------------------------------------------------------------------------------------- |
| `php knzkctl management:rebuild_stat`                      | 配信者統計の再計算を行います。                                                                           |
| `php knzkctl job:daily`                                    | デイリーワーカーを起動します。現在、ポイントの自動回復とコメント数による付与が行われます。               |
| `php knzkctl job:donate <配信ID> <ユーザID> <金額> <通貨>` | `<配信ID>` で `<ユーザID>` の コメントハイライトを有効化します。 `<通貨>` は `JPY, USD, RUB, EUR` です。 |
| `yarn start`                                               | PHP のビルトインウェブサーバを起動します。                                                               |
| `yarn ws:start`                                            | Streaming API や、TIPKnzk などのワーカーを起動します。                                                   |
| `yarn build`                                               | アセットをビルドします。(本番環境用)                                                                     |
| `yarn watch`                                               | アセットをビルドし、変更を監視します。(開発環境用)                                                       |

_その他よくわからないコマンドがあるかも_
