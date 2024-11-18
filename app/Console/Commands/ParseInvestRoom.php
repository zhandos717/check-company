<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Loan;
use App\Models\Platform;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ParseInvestRoom extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parse-invest-room';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws ConnectionException
     */
    public function handle()
    {
        $baseUrl = 'https://investroom.kz';

        $headers = [
            'DNT'    => '1',
            'Cookie' => 'XSRF-TOKEN=eyJpdiI6IjVnSC9vWE00WThQSHpTeEdlWWZLb1E9PSIsInZhbHVlIjoickpZeW5lK0tSa2FvTzJWclU1cnZCT2NhdFkvMC9MV2g5M01UNTM1L25pRzJGVmowTkZ3bzRNeHlpbFphaGtDekJSK29BVmpMM2Q4bHRsMWM5d1JrUlFwemFsKzZMQTZYY2RlU0ZYcDh4bHRyOGVMemJEZnNXM3haMmpobFQwYjciLCJtYWMiOiI2NjQ4Mzc2NmMwNjg0ZWFlNzRiMmE4YTc0YmQyMjA4ZGNlNmM4MzhkZTI4YjMyOTM4Y2FlODA5NDdhNjA5MmMwIiwidGFnIjoiIn0%3D; investroom_session=eyJpdiI6Ijh1Ui9JeWN3ekRsbXNnc3JTaVJRMUE9PSIsInZhbHVlIjoiMDNaWGRmeUhBd25ZeGdEaVE2bFdMYXcra1oyREtLNHhhL2htSEk5RDJBTWEvbW1ZK0ZMNFhGK3BDY3FCV1FGWjR4bTRFM2NMcXVXVHlObTl2VmF5SmlabEJCcFpyQVdRdU52T0R2VTNZaUREeGpuVTdrMU1pRTJMR2xLWThWTVQiLCJtYWMiOiJkYzE3OTdmMjE4NTZkZjJhZTYyODYzMjI2MTRjNzU2MWZlMGY3NGEzOWZmNjY5ZjI1YWI0ZDc5ZWU2NTk4N2QwIiwidGFnIjoiIn0%3D',
        ];

        $client = Http::withHeaders($headers)
            ->baseUrl($baseUrl)
            ->withoutVerifying();


        $request = $client->get('/loans_pubk');

        $platform = Platform::updateOrCreate([
            'link' => $baseUrl,
        ], [
            'name' => 'InvestRoom',
        ]);

        foreach ($this->findLinks($request->getBody()) as $link) {
            $response = $client->get($link);


            $info = $this->parseLoanInfo($response->getBody());

            try {
                $companyId = Company::updateOrCreate(
                    [
                        'bin' => $info['bin'],
                    ]
                    , [
                        'name' => $info['company'],
                    ]
                );

                Loan::updateOrCreate(
                    [
                        'company_id'  => $companyId->id,
                        'external_id' => explode('loan=', $link)[1],
                        'platform_id' => $platform->id,
                    ], [
                        'sum'  => str_replace(' ', '', $info['loan_amount']),
                        'paid' => false,
                    ]
                );
            } catch (\Throwable $e) {
                dump($e->getMessage(), $info);
            }
        }
    }

    public function parseLoanInfo($html): array
    {
        $result = [];

        // 1. Найти название компании
        if (preg_match('/<h5[^>]*style="[^"]*color:\s*var\(--main-text-color\)[^"]*"[^>]*>(.*?)<\/h5>/i', $html,
            $companyMatch)) {
            $result['company'] = html_entity_decode($companyMatch[1], ENT_QUOTES | ENT_HTML5);
        }

        // 2. Найти сумму займа
        if (preg_match('/<td[^>]*>Сумма:<\/td>\s*<td[^>]*>([\d\s]+)<\/td>/i', $html, $amountMatch)) {
            $result['loan_amount'] = trim($amountMatch[1]);
        }

        $bin = $this->findBinIin($html);

        if ($bin) {
            $result['bin'] = $bin;
        }

        if (preg_match('/<td[^>]*>БИН:\s*<\/td>\s*<td[^>]*>([\d\s]+)<\/td>/i', $html, $binMatch)) {
            $result['bin'] = trim($binMatch[1]);
        }

        // 3. Найти ссылку на профиль компании
        if (preg_match('/<td[^>]*>Профиль \(Адата\):<\/td>\s*<td[^>]*>\s*<a href="([^"]+)"[^>]*>/', $html,
            $linkMatch)) {
            $result['profile_link'] = $linkMatch[1];

            preg_match('/\b\d{12}\b/', $linkMatch[1], $bin);


            if (!empty($bin[0])) {
                $result['bin'] = $bin[0];
            }
        }

        return $result;
    }

    public function findBinIin($htmlContent)
    {
        $pattern = '/\b\d{12}\b/';

        // Ищем все совпадения в тексте
        preg_match_all($pattern, $htmlContent, $matches);

        // Возвращаем найденные ИИН
        return reset($matches[0]);
    }


    public function findLinks($html): array
    {
        // Регулярное выражение для поиска нужных ссылок
        $pattern = '/<a\s+href="([^"]*\/loan_invk\?loan=[^"]*)"\s+class="buttonz">Подробнее<\/a>/i';

        // Ищем все совпадения в тексте
        preg_match_all($pattern, $html, $matches);

        // Возвращаем все найденные ссылки
        return $matches[1];
    }
}
