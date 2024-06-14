<?php

namespace App\Console\Commands;

use App\Models\ApiService;
use Illuminate\Console\Command;

class AddApiServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:api_service {name} {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new API Service';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $url = $this->argument('url');
        if(ApiService::where(['name'=>$name,'url'=>$url])->first()!==null)
            $this->error("API Service '{$name}' with this url already exist");
        else {
            $apiService = ApiService::create(['name'=>$name,'url' => $url]);
            $this->info("API Service '{$name}' successfully added with ID '{$apiService->id}'.");
        }
    }
}
