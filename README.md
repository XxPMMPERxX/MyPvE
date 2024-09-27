# MyPvE

### dockerで環境構築
1. composerインストール（コード補完用）
```shell
docker run --rm --interactive --tty --volume .:/app composer install
```

2. .env.example をコピーしてポートなどを変更（任意）
```shell
cp .env.example .env
```

3. 開発サーバ起動
```shell
docker compose run pmmp /usr/bin/start-pocketmine
```
