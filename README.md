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
3. サーバ起動
```shell
docker compose up -d
```
4. data/ ディレクトリ配下に ops.txt が作成されるので、自分のユーザ名を入れる
