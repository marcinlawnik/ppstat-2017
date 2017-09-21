<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use App\Student;
use App\Console\Commands\FilterTranscode;

stream_filter_register(FilterTranscode::FILTER_NAME."*", "App\Console\Commands\FilterTranscode");
class importStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:students';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import of students from csv files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $csvLocation = 'csv'; //Folder w storage/app
            $files = Storage::files($csvLocation);

            foreach ($files as $file) {
                $studentCount = 0;
                $filePath = storage_path('app/').$file;
                echo $filePath . PHP_EOL;


                //$ct = file_get_contents($filePath);

                //dd(mb_detect_encoding($ct));
                //$fileContents = file_get_contents($filePath);


                $csv = Reader::createFromPath($filePath);
                if ($csv->isActiveStreamFilter()) {
                    $csv->appendStreamFilter(FilterTranscode::FILTER_NAME."windows-1250:utf-8");
                    //$csv->appendStreamFilter('string.toupper');
                    //$reader->appendStreamFilter('string.rot13');
                }
                //$csv = Reader::createFromString(w1250_to_utf8($fileContents));


                $csv->setDelimiter(';');
                $csv->setOffset(1); //because we don't want to insert the header
                $insert = $csv->each(function ($row) use (&$studentCount) {
                    $studentCount++;
//                dd(json_encode($row));
                    //Do not forget to validate your data before inserting it in your database
                    $student = new Student();
                    $student->imie = $row[2];
                    echo $student->imie;
                    $student->drugie_imie = $row[3];
                    $student->nazwisko = $row[1];
                    $student->imie_ojca = $row[4];
                    $student->liczba_punktow = $row[5];
                    if ($row[7] == 0) {
                        $student->jedna_gwiazdka = false;
                    } else {
                        $student->jedna_gwiazdka = true;
                    }
                    if ($row[8] == 0) {
                        $student->dwie_gwiazdki = false;
                    } else {
                        $student->dwie_gwiazdki = true;
                    }
                    $student->kierunek_id = $row[9];
                    $student->rok = $row[10];
                    //TODO FIX DATA
//                $student->wydzial_short = $row[11];
//                $student->kierunek_short = $row[13];
                    $student->save();

                    return true;
                });
                echo PHP_EOL . $studentCount . PHP_EOL;
            }
        } catch (\Exception $e) {
            dd($e->getPrevious().$e->getMessage() . $e->getLine());
        }

    }
}
