<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Editor;
use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $editors = $this->seedEditors();
            $authors = $this->seedAuthors();
            $genres = $this->seedGenres();

            foreach ($this->books() as $data) {
                $book = Book::withTrashed()->updateOrCreate(
                    ['isbn' => $data['isbn']],
                    [
                        'editor_id' => $editors[$data['editor']],
                        'title' => $data['title'],
                        'synopsis' => $data['synopsis'],
                        'total_quantity' => $data['total_quantity'],
                    ],
                );

                if ($book->trashed()) {
                    $book->restore();
                }

                $book->authors()->sync(
                    array_map(fn (string $author): int => $authors[$author], $data['authors']),
                );

                $book->genres()->sync(
                    array_map(fn (string $genre): int => $genres[$genre], $data['genres']),
                );
            }
        });
    }

    /**
     * @return array<string, int>
     */
    private function seedEditors(): array
    {
        $names = [
            'Bompiani',
            'Einaudi',
            'Feltrinelli',
            'HarperCollins',
            'Mondadori',
            'Penguin Books',
            'Rizzoli',
            'Salani',
        ];

        $editors = [];

        foreach ($names as $name) {
            $editor = Editor::withTrashed()->updateOrCreate(['name' => $name]);

            if ($editor->trashed()) {
                $editor->restore();
            }

            $editors[$name] = $editor->id;
        }

        return $editors;
    }

    /**
     * @return array<string, int>
     */
    private function seedAuthors(): array
    {
        $authorsData = [
            'alessandro-manzoni' => ['Alessandro', 'Manzoni'],
            'antoine-de-saint-exupery' => ['Antoine', 'de Saint-Exupéry'],
            'charlotte-bronte' => ['Charlotte', 'Brontë'],
            'emily-bronte' => ['Emily', 'Brontë'],
            'fedor-dostoevskij' => ['Fëdor', 'Dostoevskij'],
            'francis-scott-fitzgerald' => ['Francis Scott', 'Fitzgerald'],
            'franz-kafka' => ['Franz', 'Kafka'],
            'gabriel-garcia-marquez' => ['Gabriel García', 'Márquez'],
            'george-orwell' => ['George', 'Orwell'],
            'giuseppe-tomasi-di-lampedusa' => ['Giuseppe Tomasi', 'di Lampedusa'],
            'harper-lee' => ['Harper', 'Lee'],
            'herman-melville' => ['Herman', 'Melville'],
            'italo-calvino' => ['Italo', 'Calvino'],
            'jane-austen' => ['Jane', 'Austen'],
            'j-d-salinger' => ['J. D.', 'Salinger'],
            'j-k-rowling' => ['J. K.', 'Rowling'],
            'j-r-r-tolkien' => ['J. R. R.', 'Tolkien'],
            'lev-tolstoj' => ['Lev', 'Tolstoj'],
            'mary-shelley' => ['Mary', 'Shelley'],
            'paulo-coelho' => ['Paulo', 'Coelho'],
            'primo-levi' => ['Primo', 'Levi'],
            'ray-bradbury' => ['Ray', 'Bradbury'],
            'stefano-benni' => ['Stefano', 'Benni'],
            'umberto-eco' => ['Umberto', 'Eco'],
            'virginia-woolf' => ['Virginia', 'Woolf'],
        ];

        $authors = [];

        foreach ($authorsData as $key => [$name, $lastName]) {
            $author = Author::withTrashed()->updateOrCreate([
                'name' => $name,
                'last_name' => $lastName,
            ]);

            if ($author->trashed()) {
                $author->restore();
            }

            $authors[$key] = $author->id;
        }

        return $authors;
    }

    /**
     * @return array<string, int>
     */
    private function seedGenres(): array
    {
        $genresData = [
            'classici' => 'Classici',
            'distopia' => 'Distopia',
            'fantascienza' => 'Fantascienza',
            'fantasy' => 'Fantasy',
            'formazione' => 'Romanzo di formazione',
            'gotico' => 'Gotico',
            'memorialistica' => 'Memorialistica',
            'ragazzi' => 'Ragazzi',
            'realismo-magico' => 'Realismo magico',
            'romantico' => 'Romantico',
            'storico' => 'Romanzo storico',
        ];

        $genres = [];

        foreach ($genresData as $type => $label) {
            $genre = Genre::withTrashed()->updateOrCreate(
                ['type' => $type],
                ['label' => $label],
            );

            if ($genre->trashed()) {
                $genre->restore();
            }

            $genres[$type] = $genre->id;
        }

        return $genres;
    }

    /**
     * @return array<int, array{
     *     title: string,
     *     isbn: string,
     *     synopsis: string,
     *     total_quantity: int,
     *     editor: string,
     *     authors: array<int, string>,
     *     genres: array<int, string>
     * }>
     */
    private function books(): array
    {
        return [
            [
                'title' => '1984',
                'isbn' => '9780451524935',
                'synopsis' => 'Winston Smith vive in una società totalitaria dominata dal Grande Fratello, dove anche il pensiero è controllato.',
                'total_quantity' => 6,
                'editor' => 'Penguin Books',
                'authors' => ['george-orwell'],
                'genres' => ['classici', 'distopia'],
            ],
            [
                'title' => 'La fattoria degli animali',
                'isbn' => '9780451526342',
                'synopsis' => 'Gli animali di una fattoria si ribellano al padrone, ma la nuova società degenera in una diversa forma di tirannia.',
                'total_quantity' => 5,
                'editor' => 'Penguin Books',
                'authors' => ['george-orwell'],
                'genres' => ['classici', 'distopia'],
            ],
            [
                'title' => 'Orgoglio e pregiudizio',
                'isbn' => '9780141439518',
                'synopsis' => 'Elizabeth Bennet e Fitzwilliam Darcy devono superare orgoglio, pregiudizi e convenzioni sociali.',
                'total_quantity' => 4,
                'editor' => 'Penguin Books',
                'authors' => ['jane-austen'],
                'genres' => ['classici', 'romantico'],
            ],
            [
                'title' => 'Il grande Gatsby',
                'isbn' => '9780743273565',
                'synopsis' => 'Nella New York degli anni Venti, Jay Gatsby insegue il sogno impossibile di riconquistare Daisy Buchanan.',
                'total_quantity' => 3,
                'editor' => 'Mondadori',
                'authors' => ['francis-scott-fitzgerald'],
                'genres' => ['classici'],
            ],
            [
                'title' => 'Il buio oltre la siepe',
                'isbn' => '9780061120084',
                'synopsis' => 'Una bambina osserva il padre avvocato difendere un uomo afroamericano accusato ingiustamente nell’Alabama degli anni Trenta.',
                'total_quantity' => 5,
                'editor' => 'HarperCollins',
                'authors' => ['harper-lee'],
                'genres' => ['classici', 'formazione'],
            ],
            [
                'title' => 'Il giovane Holden',
                'isbn' => '9780316769488',
                'synopsis' => 'Holden Caulfield racconta alcuni giorni di inquietudine e ribellione trascorsi a New York.',
                'total_quantity' => 4,
                'editor' => 'Einaudi',
                'authors' => ['j-d-salinger'],
                'genres' => ['classici', 'formazione'],
            ],
            [
                'title' => 'Lo Hobbit',
                'isbn' => '9780547928227',
                'synopsis' => 'Bilbo Baggins lascia la Contea per un viaggio insieme a tredici nani e allo stregone Gandalf.',
                'total_quantity' => 7,
                'editor' => 'Bompiani',
                'authors' => ['j-r-r-tolkien'],
                'genres' => ['fantasy', 'ragazzi'],
            ],
            [
                'title' => 'Il Signore degli Anelli',
                'isbn' => '9780544003415',
                'synopsis' => 'Frodo Baggins affronta un lungo viaggio per distruggere l’Unico Anello e fermare il potere di Sauron.',
                'total_quantity' => 5,
                'editor' => 'Bompiani',
                'authors' => ['j-r-r-tolkien'],
                'genres' => ['fantasy', 'classici'],
            ],
            [
                'title' => 'Harry Potter e la pietra filosofale',
                'isbn' => '9780747532699',
                'synopsis' => 'Harry scopre di essere un mago e inizia il suo primo anno alla scuola di magia e stregoneria di Hogwarts.',
                'total_quantity' => 8,
                'editor' => 'Salani',
                'authors' => ['j-k-rowling'],
                'genres' => ['fantasy', 'ragazzi'],
            ],
            [
                'title' => 'Fahrenheit 451',
                'isbn' => '9781451673319',
                'synopsis' => 'In una società che proibisce la lettura, il pompiere Guy Montag comincia a mettere in dubbio il proprio compito.',
                'total_quantity' => 4,
                'editor' => 'Mondadori',
                'authors' => ['ray-bradbury'],
                'genres' => ['fantascienza', 'distopia'],
            ],
            [
                'title' => 'Delitto e castigo',
                'isbn' => '9780140449136',
                'synopsis' => 'Raskolnikov commette un omicidio convinto di potersi porre oltre la morale, ma deve affrontarne le conseguenze.',
                'total_quantity' => 3,
                'editor' => 'Einaudi',
                'authors' => ['fedor-dostoevskij'],
                'genres' => ['classici'],
            ],
            [
                'title' => 'Anna Karenina',
                'isbn' => '9780143035008',
                'synopsis' => 'Anna sfida le convenzioni dell’aristocrazia russa per vivere il proprio amore con il conte Vronskij.',
                'total_quantity' => 3,
                'editor' => 'Einaudi',
                'authors' => ['lev-tolstoj'],
                'genres' => ['classici', 'romantico'],
            ],
            [
                'title' => 'Moby Dick',
                'isbn' => '9780142437247',
                'synopsis' => 'Il capitano Achab guida l’equipaggio del Pequod nella sua ossessiva caccia alla balena bianca.',
                'total_quantity' => 4,
                'editor' => 'Penguin Books',
                'authors' => ['herman-melville'],
                'genres' => ['classici'],
            ],
            [
                'title' => 'Jane Eyre',
                'isbn' => '9780141441146',
                'synopsis' => 'Jane conquista la propria indipendenza e si confronta con i segreti custoditi nella dimora di Thornfield Hall.',
                'total_quantity' => 3,
                'editor' => 'Penguin Books',
                'authors' => ['charlotte-bronte'],
                'genres' => ['classici', 'romantico', 'gotico'],
            ],
            [
                'title' => 'Cime tempestose',
                'isbn' => '9780141439556',
                'synopsis' => 'La passione tormentata tra Heathcliff e Catherine segna per generazioni due famiglie dello Yorkshire.',
                'total_quantity' => 3,
                'editor' => 'Penguin Books',
                'authors' => ['emily-bronte'],
                'genres' => ['classici', 'romantico', 'gotico'],
            ],
            [
                'title' => 'Frankenstein',
                'isbn' => '9780141439471',
                'synopsis' => 'Victor Frankenstein crea un essere vivente e fugge dalle responsabilità e dalle conseguenze del proprio esperimento.',
                'total_quantity' => 5,
                'editor' => 'Penguin Books',
                'authors' => ['mary-shelley'],
                'genres' => ['classici', 'gotico', 'fantascienza'],
            ],
            [
                'title' => 'Cent’anni di solitudine',
                'isbn' => '9780060883287',
                'synopsis' => 'La storia della famiglia Buendía attraversa sette generazioni nella città immaginaria di Macondo.',
                'total_quantity' => 5,
                'editor' => 'Mondadori',
                'authors' => ['gabriel-garcia-marquez'],
                'genres' => ['classici', 'realismo-magico'],
            ],
            [
                'title' => 'Il piccolo principe',
                'isbn' => '9780156012195',
                'synopsis' => 'Un aviatore incontra nel deserto un piccolo principe arrivato da un lontano asteroide.',
                'total_quantity' => 8,
                'editor' => 'Bompiani',
                'authors' => ['antoine-de-saint-exupery'],
                'genres' => ['classici', 'ragazzi'],
            ],
            [
                'title' => 'L’alchimista',
                'isbn' => '9780061122415',
                'synopsis' => 'Il giovane pastore Santiago parte per l’Egitto alla ricerca di un tesoro e della propria leggenda personale.',
                'total_quantity' => 5,
                'editor' => 'Bompiani',
                'authors' => ['paulo-coelho'],
                'genres' => ['formazione'],
            ],
            [
                'title' => 'Il nome della rosa',
                'isbn' => '9788845292613',
                'synopsis' => 'Guglielmo da Baskerville indaga su una serie di morti misteriose in un’abbazia medievale.',
                'total_quantity' => 6,
                'editor' => 'Bompiani',
                'authors' => ['umberto-eco'],
                'genres' => ['storico'],
            ],
            [
                'title' => 'Il barone rampante',
                'isbn' => '9788804668237',
                'synopsis' => 'Cosimo Piovasco di Rondò sale sugli alberi e decide di trascorrervi tutta la vita senza rinunciare al mondo.',
                'total_quantity' => 5,
                'editor' => 'Mondadori',
                'authors' => ['italo-calvino'],
                'genres' => ['classici', 'formazione'],
            ],
            [
                'title' => 'Se questo è un uomo',
                'isbn' => '9788806219352',
                'synopsis' => 'Primo Levi testimonia la deportazione e la vita nel campo di sterminio di Auschwitz.',
                'total_quantity' => 6,
                'editor' => 'Einaudi',
                'authors' => ['primo-levi'],
                'genres' => ['classici', 'memorialistica'],
            ],
            [
                'title' => 'Il Gattopardo',
                'isbn' => '9788807882913',
                'synopsis' => 'Il principe di Salina osserva il tramonto dell’aristocrazia siciliana durante il Risorgimento.',
                'total_quantity' => 4,
                'editor' => 'Feltrinelli',
                'authors' => ['giuseppe-tomasi-di-lampedusa'],
                'genres' => ['classici', 'storico'],
            ],
            [
                'title' => 'La metamorfosi',
                'isbn' => '9780141185296',
                'synopsis' => 'Gregor Samsa si sveglia trasformato in un enorme insetto e diventa progressivamente estraneo alla propria famiglia.',
                'total_quantity' => 4,
                'editor' => 'Penguin Books',
                'authors' => ['franz-kafka'],
                'genres' => ['classici'],
            ],
            [
                'title' => 'La signora Dalloway',
                'isbn' => '9780141182490',
                'synopsis' => 'Durante una giornata londinese, Clarissa Dalloway prepara una festa mentre ricordi e vite parallele si intrecciano.',
                'total_quantity' => 3,
                'editor' => 'Penguin Books',
                'authors' => ['virginia-woolf'],
                'genres' => ['classici'],
            ],
            [
                'title' => 'I promessi sposi',
                'isbn' => '9788807900235',
                'synopsis' => 'Renzo e Lucia affrontano soprusi, guerra e peste per riuscire a celebrare il loro matrimonio.',
                'total_quantity' => 7,
                'editor' => 'Feltrinelli',
                'authors' => ['alessandro-manzoni'],
                'genres' => ['classici', 'storico'],
            ],
            [
                'title' => 'Bar Sport',
                'isbn' => '9788807881831',
                'synopsis' => 'Una raccolta ironica di personaggi, abitudini e racconti che animano il tipico bar italiano.',
                'total_quantity' => 4,
                'editor' => 'Feltrinelli',
                'authors' => ['stefano-benni'],
                'genres' => ['classici'],
            ],
        ];
    }
}
