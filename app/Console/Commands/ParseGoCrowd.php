<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Loan;
use App\Models\Platform;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ParseGoCrowd extends Command
{
    protected $signature = 'app:parse-go-crowd';

    protected $description = 'Command description';

    public function handle()
    {
        $url = 'https://gocrowd.io';

        $response = Http::get('https://gocrowd.io/api/v2/offerings',
            'tlimit=1000&fields[]=name&fields[]=id&fields[]=funded_amount&fields[]=details');


        $platform = Platform::updateOrCreate([
            'link' => $url,
        ], [
            'name' => 'GoCrowd',
        ]);

        foreach ($response->json() as $offering) {
            $companyId = Company::updateOrCreate(
                [
                    'bin' => $this->findBinIin($offering['details']),
                ]
                , [
                    'name' => $offering['name'],
                ]
            );

            Loan::updateOrCreate(
                [
                    'company_id'  => $companyId->id,
                    'external_id' => $offering['id'],
                    'platform_id' => $platform->id,
                ], [
                    'sum'  => str_replace(' ', '', $offering['funded_amount']),
                    'paid' => false,
                ]
            );
        }
    }

    public function findBinIin($htmlContent)
    {
        $pattern = '/\b\d{12}\b/';

        // Ищем все совпадения в тексте
        preg_match_all($pattern, $htmlContent, $matches);

        // Возвращаем найденные ИИН
        return reset($matches[0]);
    }

}
