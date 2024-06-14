<?php

namespace App\Console\Commands;

use App\Models\Account;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Income;
use App\Models\Stock;

class FetchDataFromAPI extends Command
{
    protected $signature = 'fetch:data {account_id} {table? : is optional. Can only be incomes,stocks,orders,sales or all(default)}';
    protected $description = 'Fetch data from external API';

    protected $modelMap = [
        'orders' => Order::class,
        'sales' => Sale::class,
        'incomes' => Income::class,
        'stocks' => Stock::class,
    ];

    protected $endpoints = [
        'orders' => 'orders',
        'sales' => 'sales',
        'incomes' => 'incomes',
        'stocks' => 'stocks',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $account_id = $this->argument('account_id');
        $account = Account::find($account_id);
        if(!$account){
            $this->error("Account not found!");
            return;
        }
        $this->info("Your account is '{$account->username}'.");
        $table = $this->argument('table');
        switch ($table) {
            case 'incomes':
            case 'stocks':
            case 'orders':
            case 'sales':
                $this->fetchData($table, $account);
                break;
            case 'all':
            case null:
                $this->fetchData('incomes', $account);
                $this->fetchData('stocks', $account);
                $this->fetchData('orders', $account);
                $this->fetchData('sales', $account);
                break;
            default:
                $this->error("The 'table' parameter wrong! Use fetch:data --help");
                return;
        }
        $this->info('Done! Everything was successful!');
    }

    //получаем данные из API и вызываем функцию "перелива" их в БД
    protected function fetchData(string $dataType, $account): void
    {
        $this->info("Starting to fetch {$dataType} data using account {$account->username}(ID {$account->id}).");

        // Получаем все токены аккаунта
        $tokens = $account->tokens;
        if ($tokens->isEmpty()) {
            $this->error("No API tokens found for the account!");
            return;
        }
        //Используя каждый токен сливаем данные из апи, к которому токен относится
        foreach ($tokens as $token) {
            $tokenType = $token->token_type->type;
            $tokenValue = $token->token;

            $endpoint = $this->endpoints[$dataType];
            $dateTo = now()->format('Y-m-d');
            //Получаем только свежие данные. За последнюю неделю или только за сегодня, если это склады.
            $dateFrom = $endpoint==='stocks'? $dateTo : now()->subDays(7)->format('Y-m-d');
            $limit = '500';
            $page = 1;

            $modelClass = $this->modelMap[$dataType];
            $apiUrl = $token->api_service->url;
            $queryParams = "?dateFrom={$dateFrom}&dateTo={$dateTo}&{$tokenType}={$tokenValue}&limit={$limit}";

            try {
                $response = Http::get("{$apiUrl}{$endpoint}{$queryParams}&page={$page}");
                $data = $response->json();

                $totalPages = $data['meta']['last_page'];

                $this->processPageData($data['data'], $modelClass, $account->id);
//                $this->info(ucfirst($dataType) . " page {$page} of {$totalPages} processed.");

                for ($page += 1; $page <= $totalPages; $page++) {
                    while (true) {
                        $response = Http::get("{$apiUrl}{$endpoint}{$queryParams}&page={$page}");
                        if ($response->status() === 429) {
                            $this->info('Too many requests, retrying in 30 seconds...');
                            sleep(30);
                        }elseif ($response->status() !== 200){
                            $this->info($response->status());
                        } else {
                            break;
                        }
                    }
                    $data = $response->json('data');
                    $this->processPageData($data, $modelClass, $account->id);
//                    $this->info(ucfirst($dataType) . " page {$page} of {$totalPages} processed.");
                }
                $this->info(ucfirst($dataType) . " processed.");
            } catch (RequestException $e) {
                $this->error('Error fetching data: ' . $e->getMessage());
            }
        }
    }

    //Функция перелива данных в БД
    protected function processPageData(array $data, string $modelClass, int $accountId): void
    {
        foreach ($data as $item) {
            // Добавляем account_id к данным перед сохранением
            $item['account_id'] = $accountId;
            // Создаем новую строку в БД с указанными данными, если её нет
            $modelClass::firstOrCreate($item);
        }
    }
}