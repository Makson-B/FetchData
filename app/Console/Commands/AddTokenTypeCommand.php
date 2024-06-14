<?php

namespace App\Console\Commands;

use App\Models\TokenType;
use Illuminate\Console\Command;

class AddTokenTypeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:token_type {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new type of Token';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $type = $this->argument('type');
        if(TokenType::where(['type'=>$type])->first()!==null)
            $this->error("Token type '{$type}' already exist");
        else {
            $tokenType = TokenType::create(['type' => $type]);
            $this->info("Token type '{$type}' successfully added with ID='{$tokenType->id}'.");
        }
    }
}
