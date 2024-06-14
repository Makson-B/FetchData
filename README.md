<h1 align="center">Приветствую на странице моего приложения <a href="https://github.com/Makson-B/FetchData/tree/master" target="_blank">FetchData</a>
<img src="https://github.com/blackcater/blackcater/raw/main/images/Hi.gif" height="32"/></h1>

### Кратко о приложении:
##### Позволяет добавить в базу данных компании, их аккаунты, API сервисы, тип токенов и сами токены.
##### Используя добавленные данные можно стянуть в нашу БД данные с эндпоинтов incomes, stocks, orders и sales API сервисов.

### Команды для использования:
#### Для добавления новых компаний:
```bash
php artisan add:company {Company_Name}
```

##### В ответ получаем строку по типу:
###### Company '{Company_Name}' successfully added with ID '{Company_ID}'.
##### Откуда можем узнать id компании, которую создали. Далее по той же схеме...

#### Для добавления аккаунтов компании:
```bash
php artisan add:account {Company_ID} {Username}
```
##### В ответе: 
###### Account '{Username}' with ID '{Account_ID}' successfully added to company '{Company_name}'.

#### Для добавления API сервиса:
```bash
php artisan add:api_service {API_Service_name} {API_Service_Url}
```
##### В ответе: 
###### API Service '{API_Service_name}' successfully added with ID '{API Service ID}'.

#### Для добавления типа токена:
```bash
php artisan add: add:token_type {Token_type}
```
##### В ответе: 
###### Token type '{Token_type}' successfully added with ID='{Token type ID}'.

#### Для добавления токена:
```bash
php artisan add:token {Token_type_ID} {Token} {Account_ID} {API_Service_ID}
```
##### В ответе: 
###### New API Token(ID='{Token_ID}') with token type '{Token_type}' successfully added for account '{Username}' and API service '{API_Service_name}'.

#### Команда для стягивания данных:
```bash
php artisan fetch:data {Account_ID} {Data_type?}
```
##### Data_type - опциональный параметр для выбора какой тип данных хотим стянуть. 
###### Может быть incomes, stocks, orders, sales или же all.
##### Если оставить параметр пустым, то будет стягиваться все типы данных, как и при all.

##### В ответе будет: 
###### Your account is '{Account_name}'.
###### Starting to fetch {Data_type} data using account {Account_name}(ID {Account_ID}).
##### //выполнение стягивания данных//
###### {Data_type} processed.
###### Done! Everything was successful!

### Стек:
- docker/docker-compose
- php 8.2
- mysql 8.0
- Laravel 11
