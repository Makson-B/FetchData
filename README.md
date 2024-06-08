Доступы к БД:
DB_CONNECTION=mysql
DB_HOST=mysql-test1234-test-1234.k.aivencloud.com
DB_PORT=23307
DB_DATABASE=defaultdb
DB_USERNAME=avnadmin
DB_PASSWORD=AVNS_6yDMlm9CFOqW2Ko6G2Y

Была реализована комманда, с помощью которой можно стянуть
все данные по описанным эндпоинтам и сохранить в БД.
Причём есть 2 варианта: fetch:data - сохраняются файлы в формате json, fetch:data sorted - распределябтся по столбцам.
Названия таблиц sales, orders, incomes, stocks и sorted_sales, sorted_orders, sorted_incomes, sorted_stocks соответственно.
