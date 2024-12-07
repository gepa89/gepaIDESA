<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id(); // id (Primary Key, Auto Increment)
            $table->string('title'); // title (VARCHAR)
            $table->string('isbn')->unique(); // isbn (VARCHAR, unique)
            $table->date('published_date')->nullable(); // published_date (DATE, nullable)
            $table->unsignedBigInteger('author_id'); // author_id (Foreign Key)
            $table->timestamps(); // created_at, updated_at

            // Foreign Key Constraint
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('books');
    }
}

