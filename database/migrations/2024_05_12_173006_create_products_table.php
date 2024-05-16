<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description');
            $table->unsignedBigInteger('price')->nullable();
            $table->string('image', 255);
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->date('expired_at');
            $table->string('modified_by', 255)->nullable()->comment('Email user');
            $table->timestamps();
        });

        // Creating the trigger to enforce maximum length of 11 digits for the price column
        DB::unprepared('
            CREATE TRIGGER products_price_length_trigger BEFORE INSERT ON products
            FOR EACH ROW
            BEGIN
                IF CHAR_LENGTH(NEW.price) > 11 THEN
                    SIGNAL SQLSTATE \'45000\' SET MESSAGE_TEXT = \'Price exceeds maximum length of 11 digits\';
                END IF;
            END;
        ');
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}