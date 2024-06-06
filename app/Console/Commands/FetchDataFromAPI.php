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

    protected function fetchData(string $dataType, bool $sorted = false): void
    {
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
        $this->info(ucfirst($dataType) . " page {$page} of {$totalPages} processed.");

        for ($page += 1; $page <= $totalPages; $page++) {
            $response = Http::get("{$apiUrl}{$endpoint}?dateFrom={$dateFrom}&dateTo={$dateTo}&page={$page}&key={$token}&limit={$limit}");
            $data = $response->json();
            $this->processPageData($data['data'], $modelClass, $sorted);
//            $this->info(ucfirst($dataType) . " page {$page} of {$totalPages} processed.");
        }
        $this->info(ucfirst($dataType) . " processed.");
    }

    protected function processPageData(array $data, string $modelClass, bool $sorted): void
    {
        if ($sorted) {
            foreach ($data as $item) {
                // Создаем новый экземпляр модели с указанными данными
                $newModel = $modelClass::create($item);

                // Проверяем, существует ли уже такая запись в базе данных
                if (!$newModel->exists) {
                    // Если записи нет, сохраняем её
                    $newModel->save();
                }
            }
        } else {
            // Если данные не сортированные, сохраняем их пачками json
            $dataChunks = array_chunk($data, 100);
            foreach ($dataChunks as $chunk) {
                $modelClass::create(['data' => json_encode($chunk)]);
            }
            sleep(1);
        }
    }
}