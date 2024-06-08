<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\SortedOrder;
use App\Models\SortedSale;
use App\Models\SortedIncome;
use App\Models\SortedStock;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Income;
use App\Models\Stock;
use Illuminate\Validation\ValidationException;

class FetchDataFromAPI extends Command
{
    protected $signature = 'fetch:data {sorted?} {table?}';
    protected $description = 'Fetch data from external API';

    protected $modelMap = [
        'orders' => Order::class,
        'sales' => Sale::class,
        'incomes' => Income::class,
        'stocks' => Stock::class,
    ];

    protected $sortedModelMap = [
        'orders' => SortedOrder::class,
        'sales' => SortedSale::class,
        'incomes' => SortedIncome::class,
        'stocks' => SortedStock::class,
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
        $sorted = $this->argument('sorted');
        $table = $this->argument('table');

        //Проверка вызванной команды и вызов соответствующей функции "перелива" данных из API в DB
        if (!isset($sorted) && !isset($table)) {
            // Нет аргументов: загрузить все данные без сортировки
            $this->fetchAllData();
        } elseif ($sorted == 'sorted' && !isset($table)) {
            // Указан только аргумент 'sorted', но нет таблицы
            $this->fetchAllData(true);
        } elseif ($sorted == 'sorted' && isset($table)) {
            // Указан аргумент 'sorted' и таблица
            if (isset($this->modelMap[$table])) {
                $this->fetchData($table, true);
            } else {
                throw ValidationException::withMessages(['Command is incorrect! Use fetch:data sorted {table_name}.']);
            }
        } elseif (isset($this->modelMap[$sorted])) {
            // Указана только таблица (без sorted)
            $this->fetchData($sorted);
        } else {
            throw ValidationException::withMessages(['Command is incorrect! Use fetch:data sorted {table_name} or fetch:data {table_name}.']);
        }

        $this->info('Data fetch completed.');
    }

    protected function fetchAllData(bool $sorted = false): void
    {
        $this->fetchData('orders', $sorted);
        $this->fetchData('sales', $sorted);
        $this->fetchData('incomes', $sorted);
        $this->fetchData('stocks', $sorted);
    }

    //получаем данные из API и вызываем функцию "перелива" их в БД
    protected function fetchData(string $dataType, bool $sorted = false): void
    {
        //данные для запроса
        $token = config('api.api_key');
        $apiUrl = config('api.api_url');
        $dateFrom = $dataType === 'stocks' ? now()->format('Y-m-d') : '1900-01-01';
        $dateTo = now()->format('Y-m-d');
        $limit = '500';
        $page = 1;
        $endpoint = $this->endpoints[$dataType];

        $modelClass = $sorted ? $this->sortedModelMap[$dataType] : $this->modelMap[$dataType];

        $response = Http::get("{$apiUrl}{$endpoint}?dateFrom={$dateFrom}&dateTo={$dateTo}&page={$page}&key={$token}&limit={$limit}");
        $data = $response->json();

        $totalPages = $data['meta']['last_page'];

        $this->processPageData($data['data'], $modelClass, $sorted);
//        $this->info(ucfirst($dataType) . " page {$page} of {$totalPages} processed.");

        for ($page += 1; $page <= $totalPages; $page++) {
            $response = Http::get("{$apiUrl}{$endpoint}?dateFrom={$dateFrom}&dateTo={$dateTo}&page={$page}&key={$token}&limit={$limit}");
            $data = $response->json();
            $this->processPageData($data['data'], $modelClass, $sorted);
//            $this->info(ucfirst($dataType) . " page {$page} of {$totalPages} processed.");
        }
        $this->info(ucfirst($dataType) . " processed.");
    }

    //Функция перелива данных в БД
    protected function processPageData(array $data, string $modelClass, bool $sorted): void
    {
        if ($sorted) {
            //Вариант, если нужно сохранить отсортированные данные
            foreach ($data as $item) {
                // Создаем новую строку в БД с указанными данными, если её нет
                $modelClass::firstOrCreate($item);
            }
        } else {
            //Вариант, если данные сохранять в json без сортировки
            //Проверяем данные и удаляем уже имеющиеся в БД, чтобы не повторять
            foreach ($data as $key => $data_item) {
                $exists = $modelClass::whereRaw('JSON_CONTAINS(data, ?)', json_encode($data_item))->exists();
                if ($exists) {
                    unset($data[$key]);
                }
            }
            // Сохраняем данные пачками json по 100, чтобы корректно помещались
            $dataChunks = array_chunk($data, 100);
            foreach ($dataChunks as $chunk) {
                $modelClass::firstOrCreate(['data' => json_encode($chunk)]);
            }
            sleep(1);
        }
    }
}