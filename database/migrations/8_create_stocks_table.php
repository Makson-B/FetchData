<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->date('last_change_date');
            $table->string('supplier_article');
            $table->string('tech_size');
            $table->string('barcode');
            $table->integer('quantity');
            $table->boolean('is_supply');
            $table->boolean('is_realization');
            $table->integer('quantity_full');
            $table->string('warehouse_name');
            $table->integer('in_way_to_client');
            $table->integer('in_way_from_client');
            $table->unsignedBigInteger('nm_id');
            $table->string('subject');
            $table->string('category');
            $table->string('brand');
            $table->string('sc_code');
            $table->decimal('price', 10, 2);
            $table->decimal('discount', 5, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stocks');
    }
};
