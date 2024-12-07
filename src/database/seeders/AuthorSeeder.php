<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Author;
class AuthorSeeder extends Seeder
{
    public function run()
    {
        $authors = [
            [
                'name' => 'J.K. Rowling',
                'birthdate' => '1965-07-31',
                'nationality' => 'British',
            ],
            [
                'name' => 'George Orwell',
                'birthdate' => '1903-06-25',
                'nationality' => 'British',
            ],
            [
                'name' => 'Jane Austen',
                'birthdate' => '1775-12-16',
                'nationality' => 'British',
            ],
            [
                'name' => 'Mark Twain',
                'birthdate' => '1835-11-30',
                'nationality' => 'American',
            ],
            [
                'name' => 'Ernest Hemingway',
                'birthdate' => '1899-07-21',
                'nationality' => 'American',
            ],
            [
                'name' => 'Gabriel García Márquez',
                'birthdate' => '1927-03-06',
                'nationality' => 'Colombian',
            ],
            [
                'name' => 'Isabel Allende',
                'birthdate' => '1942-08-02',
                'nationality' => 'Chilean',
            ],
            [
                'name' => 'Haruki Murakami',
                'birthdate' => '1949-01-12',
                'nationality' => 'Japanese',
            ],
            [
                'name' => 'Chinua Achebe',
                'birthdate' => '1930-11-16',
                'nationality' => 'Nigerian',
            ],
            [
                'name' => 'Toni Morrison',
                'birthdate' => '1931-02-18',
                'nationality' => 'American',
            ],
        ];

        foreach ($authors as $author) {
            Author::create($author);
        }
    }
}
