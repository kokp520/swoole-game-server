

## About swoole-game-server

## log book

 - **20241018**

#### install laravel
composer create-project --prefer-dist laravel/laravel laravel-app

#### install laravel-s
composer require hhxsv5/laravel-s

#### new config publish
php artisan laravels publish

#### create websocket Handler Controller 
config/laravels.php should setting route

20241020
從portal進入後會建立laravels class是由hhxsv5/laravel-s的server建立swoole\Server的

20241021
更新laravels設定
以及artisan laravels console setting顯示畫面
指定port and server type決定開啟服務器

20241022
修改storage/laravels.conf 改名為laravels.conf.temp
php artisan config:clear
php artisan config:cache
得以重新序列化

待辦清單：
- 開放多個server laraverls
- 設定多個服務器路由
- docker化可以放最後, swoole設定有點麻煩
- nginx setting
- 開放多個socket服務

紀錄note
- swooleTable 綁定fd與uid, 可選，也可以用全局儲存 Redis\Memcached\Mysql 但注意多個swoole server fd可能衝突
- nginx 