<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;

class BookSeeder extends Seeder
{
    public function run()
    {
        $books = [
            [
                'title' => 'Harry Potter and the Philosopher\'s Stone',
                'isbn' => '978-0747532699',
                'published_date' => '1997-06-26',
                'author_id' => 1, // J.K. Rowling
            ],
            [
                'title' => '1984',
                'isbn' => '978-0451524935',
                'published_date' => '1949-06-08',
                'author_id' => 2, // George Orwell
            ],
            [
                'title' => 'Pride and Prejudice',
                'isbn' => '978-0679783268',
                'published_date' => '1813-01-28',
                'author_id' => 3, // Jane Austen
            ],
            [
                'title' => 'Adventures of Huckleberry Finn',
                'isbn' => '978-0486280615',
                'published_date' => '1884-12-10',
                'author_id' => 4, // Mark Twain
            ],
            [
                'title' => 'The Old Man and the Sea',
                'isbn' => '978-0684801223',
                'published_date' => '1952-09-01',
                'author_id' => 5, // Ernest Hemingway
            ],
            [
                'title' => 'One Hundred Years of Solitude',
                'isbn' => '978-0060883287',
                'published_date' => '1967-06-05',
                'author_id' => 6, // Gabriel García Márquez
            ],
            [
                'title' => 'The House of the Spirits',
                'isbn' => '978-1501117015',
                'published_date' => '1982-01-01',
                'author_id' => 7, // Isabel Allende
            ],
            [
                'title' => 'Norwegian Wood',
                'isbn' => '978-0375704024',
                'published_date' => '1987-09-04',
                'author_id' => 8, // Haruki Murakami
            ],
            [
                'title' => 'Things Fall Apart',
                'isbn' => '978-0385474542',
                'published_date' => '1958-06-17',
                'author_id' => 9, // Chinua Achebe
            ],
            [
                'title' => 'Beloved',
                'isbn' => '978-1400033416',
                'published_date' => '1987-09-16',
                'author_id' => 10, // Toni Morrison
            ],
        ];

        foreach ($books as $book) {
            Book::create($book);
        }
    }
}