<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthorsTable extends Migration
{
    public function up()
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id(); // id (Primary Key, Auto Increment)
            $table->string('name'); // name (VARCHAR)
            $table->date('birthdate')->nullable(); // birthdate (DATE, nullable)
            $table->string('nationality')->nullable(); // nationality (VARCHAR, nullable)
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('authors');
    }
}
