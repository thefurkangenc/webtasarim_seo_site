<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV indirme yanıtı. Satırlar akıtılır (generator/iterable), böylece binlerce
 * kayıt belleğe toplanmaz.
 *
 * Ayırıcı noktalı virgül: Türkçe Windows/Excel kurulumu varsayılan olarak onu
 * bekler, virgülle ayrılmış dosyayı tek kolonda açar.
 */
class Csv
{
    public static function download(string $filename, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'wb');

            // Excel'in UTF-8'i doğru okuması için BOM.
            fwrite($handle, "\xEF\xBB\xBF");

            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
