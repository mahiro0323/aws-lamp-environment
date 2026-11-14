# LAMP環境の構築手順

## 1. 前提

- OS: AlmaLinux 9
- EC2にElastic IPを関連付け済み
- Route 53のHosted Zoneを作成済み
- Security Groupは必要最小限の通信のみ許可

## 2. Security Group

| 用途 | プロトコル | ポート | 接続元 |
|---|---|---:|---|
| SSH | TCP | 22 | 自身のグローバルIPのみ |
| HTTP | TCP | 80 | `0.0.0.0/0`、`::/0` |
| HTTPS | TCP | 443 | `0.0.0.0/0`、`::/0` |

> SSHの22番ポートを全世界に公開しないようにします。

## 3. パッケージ更新

```bash
sudo dnf update -y
```

## 4. Apache

```bash
sudo dnf install -y httpd
sudo systemctl enable --now httpd
sudo systemctl status httpd
```

構文確認:

```bash
sudo httpd -t
```

## 5. MySQL

利用するMySQLリポジトリとOSの対応を確認してからインストールします。

```bash
sudo systemctl enable --now mysqld
sudo systemctl status mysqld
```

初期設定では、不要な匿名ユーザーやテスト用データベースを削除し、管理者パスワードを安全に設定します。

## 6. PHP

```bash
sudo dnf install -y php php-mysqlnd
sudo systemctl restart httpd
php -v
```

動作確認用ファイルの例:

```php
<?php
echo 'LAMP environment is running.';
```

## 7. VirtualHost

実際の構成から認証情報を除いたサンプルを [`config/httpd/mahiroaws.website.conf.example`](../config/httpd/mahiroaws.website.conf.example) に掲載しています。

設定後は必ず構文を確認します。

```bash
sudo httpd -t
sudo systemctl restart httpd
```

## 8. Route 53

Route 53のHosted Zoneに、独自ドメインからElastic IPへ向けたAレコードを作成します。

確認例:

```bash
dig mahiroaws.website
curl -I http://mahiroaws.website
```

## 9. HTTPS

SSL/TLS証明書をWebサーバーへ設定し、Security Groupで443番ポートを許可します。

確認例:

```bash
sudo httpd -t
sudo systemctl restart httpd
curl -I https://mahiroaws.website
```

最終確認ではHTTP 200の応答、証明書の対象ドメイン、有効期限を確認します。

## 10. 基本確認コマンド

```bash
sudo systemctl status httpd
sudo systemctl status mysqld
sudo ss -lntp
sudo journalctl -u httpd --no-pager -n 50
sudo tail -n 50 /var/log/httpd/error_log
curl -I http://localhost
curl -I https://mahiroaws.website
```

