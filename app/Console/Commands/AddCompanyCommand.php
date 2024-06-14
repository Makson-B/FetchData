<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class AddCompanyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:company {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new company';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        if(Company::where(['name'=>$name])->first()!==null)
            $this->error("Company '{$name}' already exist");
        else {
            $company = Company::create(['name' => $name]);
            $this->info("Company '{$name}' successfully added with ID '{$company->id}'.");
        }
    }
}
