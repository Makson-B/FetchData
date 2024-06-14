<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\ApiService;
use App\Models\ApiToken;
use App\Models\TokenType;
use Illuminate\Console\Command;

class AddApiTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:token {token_type_id} {token} {account_id} {api_service_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new API Token';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $token_type_id = $this->argument('token_type_id');
        $token_type = TokenType::find($token_type_id);
        if ($token_type===null) {
            $this->error("Token type with ID='{$token_type_id}' not found.");
            return;
        }
        $account_id = $this->argument('account_id');
        $account = Account::find($account_id);
        if ($account===null) {
            $this->error("Account with ID='{$account_id}' not found.");
            return;
        }
        $api_service_id = $this->argument('api_service_id');
        $api_service = ApiService::find($api_service_id);
        if ($api_service===null) {
            $this->error("API Service with ID='{$api_service_id}' not found.");
            return;
        }

        $token = $this->argument('token');
        if(ApiToken::where([
                'account_id'=>$account_id,
                'api_service_id'=>$api_service_id,
                'token_type_id'=>$token_type_id,
                'token'=>$token])->first()!==null)
            $this->error("API Token with token type '{$token_type->type}' already exist for account '{$account->username}' and API service '{$api_service->name}'");
        else {
            $apiToken = ApiToken::create([
                'account_id' => $account_id,
                'api_service_id' => $api_service_id,
                'token_type_id' => $token_type_id,
                'token' => $token
            ]);
            $this->info("'New API Token(ID='{$apiToken->id}') with token type '{$token_type->type}' successfully added for account '{$account->username}' and API service '{$api_service->name}'.");
        }
    }
}
