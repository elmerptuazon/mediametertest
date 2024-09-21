<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\Csv\Reader; 
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreFileRequest;

class UploadCsvController extends Controller
{
    public function index(Request $request)
    {
        $jsonData = Storage::get('medalists/data.json');
        $data = json_decode($jsonData, true);

        $perPage = 10;
        $currentPage = $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;

        $pagedData = array_slice($data, $offset, $perPage);
        $total = count($data);
        $totalPages = ceil($total / $perPage);

        $response = [
            'data' => $pagedData,
            'paginate' => [
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'next_page' => $currentPage < $totalPages ? route('medalists.index', ['page' => $currentPage + 1]) : null,
                'previous_page' => $currentPage > 1 ? route('medalists.index', ['page' => $currentPage - 1]) : null,
            ],
        ];

        return response()->json($response);
    }

    public function uploadCsv(StoreFileRequest $request) 
    {
        $fileName = $request->file('file')->getClientOriginalName();
        $path = $request->file('file')->storeAs('medalists', $fileName);

        $csv = Reader::createFromPath(storage_path('app/' . $path), 'r');
        $csv->setHeaderOffset(0);

        $data = $this->storeAsJson($csv);

        array_walk_recursive($data, function (&$value) {
            if (!mb_check_encoding($value, 'UTF-8')) {
                $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            }
        });

        $jsonData = json_encode(array_values($data), JSON_PRETTY_PRINT);
        
        if ($jsonData === false) {
            Log::error('JSON encoding error: ' . json_last_error_msg());
            return response()->json('Failed to encode data to JSON: ' . json_last_error_msg(), 500);
        }

        $jsonFilePath = 'medalists/data.json';
        Storage::disk('local')->put($jsonFilePath, $jsonData);

        return response()->json('CSV file imported successfully.', 200);
    }

    private function storeAsJson($csv)
    {
        $data = [];

        foreach ($csv as $record) {
            if (!isset($record['discipline'], $record['event'], $record['medal_date'])) {
                Log::warning('Missing fields in record: ' . json_encode($record));
                continue; 
            }

            $discipline = $record['discipline'];
            $event = $record['event'];
            $medalDate = $record['medal_date'];

            if (!isset($data[$discipline])) {
                $data[$discipline] = [
                    'discipline' => $discipline,
                    'event' => $event,
                    'event_date' => $medalDate,
                    'medalists' => [],
                ];
            }

            $data[$discipline]['medalists'][] = [
                'name' => $record['name'],
                'medal_type' => $record['medal_type'],
                'gender' => $record['gender'],
                'country' => $record['country'],
                'country_code' => $record['country_code'],
                'nationality' => $record['nationality'],
                'medal_code' => $record['medal_code'],
            ];
        }

        Log::info('Data structure before JSON encoding: ', $data); 

        return $data;
    }

}
