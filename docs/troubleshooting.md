# 障害対応記録

## 1. Apacheが起動しない

### 事象

Apacheを起動または再起動しようとすると失敗し、Webページへアクセスできない状態になりました。

### 切り分け

```bash
sudo systemctl status httpd
sudo journalctl -u httpd --no-pager -n 50
sudo httpd -t
```

AWS側では、EC2のステータスチェックとSecurity Groupの80・443番ポートも確認しました。

### 原因

Apacheの`ssl.conf`で、存在しない証明書ファイル`localhost.crt`を参照していました。このため設定エラー`AH00526`が発生し、Apacheが起動できませんでした。

### 対応

1. 設定ファイルをバックアップ
2. 証明書ファイルの参照先を確認
3. 不正な参照を無効化または正しい証明書へ修正
4. `httpd -t`で構文確認
5. Apacheを再起動

```bash
sudo cp /etc/httpd/conf.d/ssl.conf /etc/httpd/conf.d/ssl.conf.bak
sudo httpd -t
sudo systemctl restart httpd
sudo systemctl status httpd
```

### 結果

Apacheが正常起動し、HTTPアクセスが可能になりました。

## 2. 403 Forbidden

### 事象

Apache起動後も、ブラウザには403 Forbiddenが表示されました。

### 確認項目

- VirtualHostの`DocumentRoot`
- 公開ディレクトリと`index.html`の有無
- ファイルとディレクトリの所有者・権限
- Apacheの`<Directory>`設定
- SELinuxの状態とコンテキスト

### 対応

公開ディレクトリとインデックスファイルを確認し、Apacheが読み取れる状態へ修正しました。

## 3. HTTPSで接続できない

### 事象

HTTPでは接続できるものの、HTTPSでは接続できませんでした。

### 確認項目

- Security GroupでTCP/443が許可されているか
- Apacheが443番ポートでListenしているか
- 証明書と秘密鍵の参照先が正しいか
- 証明書の対象ドメインが一致しているか
- DNSが正しいElastic IPを返しているか

```bash
sudo ss -lntp
sudo httpd -t
dig mahiroaws.website
curl -Iv https://mahiroaws.website
```

### 結果

SSL設定と443番ポートを見直し、最終的にHTTPSでHTTP 200の応答を確認しました。

## 切り分けから得た教訓

障害対応では、次の順番で確認すると原因の範囲を狭めやすいと学びました。

1. AWSリソースの状態
2. Security Groupと通信経路
3. OS上のサービス状態
4. 設定ファイルの構文
5. ログのエラーメッセージ
6. ファイルの配置・権限
7. DNSとSSL/TLS

