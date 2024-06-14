<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Company;
use Illuminate\Console\Command;

class AddAccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:account {company_id} {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a nem account to a company';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $company_id = $this->argument('company_id');
        $company = Company::find($company_id);
        if (!$company) {
            $this->error("Company with ID '{$company_id}' not found.");
            return;
        }
        $name = $this->argument('username');
        if(Account::where(['company_id'=>$company_id,'username'=>$name])->first()!==null) {
            $this->error("Account '{$name}' for company '{$company->name}' already exist");
            return;
        }
        else
            $account = Account::create([
                'company_id' => $company_id,
                'username' => $name
            ]);
        $this->info("Account '{$name}' with ID '{$account->id}' successfully added to company '{$company->name}'.");
    }
}
