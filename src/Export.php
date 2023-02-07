<?php

namespace Youfront\Export;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\Writer;
use Maatwebsite\Excel\Facades\Excel;
use Statamic\Actions\Action;
use Throwable;

class Export extends Action
{
    /**
     * Title
     */
    public static function title()
    {
        return __('Export');
    }

    /**
     * Download items, based on configuration for specific collections. Defaults to CSV.
     *
     * @param $items
     * @param $values
     * @return false|Response
     * @throws CannotInsertRecord
     * @throws Exception
     */
    public function download($items, $values)
    {
        try {
            $export_type = config("statamic-export.collections.{$items[0]->collection->handle}", config("statamic-export.default", "csv"));
        } catch (Throwable $t) {
            $export_type = config("statamic-export.default", "csv");
        }

        $headers = $this::getHeaders($items);
        $entries = $this::getEntries($headers, $items);
        switch ($export_type):
            case "csv":
                $csv = Writer::createFromString('');
                $csv->insertOne($headers->toArray());
                $csv->insertAll($entries->toArray());

                return new Response($csv->toString(), 200, [
                    'Content-Disposition' => 'attachment; filename="' . $this::getFileName($export_type) . '"',
                ]);

                break;
            case "json":
                $data = $items->map(function ($item) {
                    return $item->data()->except(config("statamic-export.excluded_columns", []));
                });

                return response()
                    ->json($data)
                    ->header('Content-Disposition', 'attachment; filename="' . $this::getFileName($export_type) . '"');
                break;
            case "xlsx":
                return Excel::download(new ArrayExport($headers->toArray(), $entries->toArray()), $this::getFileName($export_type));
                break;
        endswitch;
    }

    public function authorize($user, $item)
    {
        return $user->can('view', $item);
    }

    /**
     * Get array keys as headers.
     *
     * @param Collection $items
     *
     * @return Collection
     */
    private static function getHeaders(Collection $items)
    {
        $headers = new Collection;

        $headers->push('id');

        foreach ($items as $item) {
            foreach ($item->data()->keys() as $key) {
                if (in_array($key, config("statamic-export.excluded_columns", []))) {
                    continue;
                }

                $headers->push($key);
            }
        }

        return $headers = $headers->unique();
    }

    /**
     * Get entries values by headers.
     *
     * @param Collection $headers
     * @param Collection $items
     *
     * @return Collection
     */
    private static function getEntries(Collection $headers, Collection $items)
    {
        $data = new Collection;

        foreach ($items as $item) {
            $itemData = [];

            foreach ($headers as $header) {
                if (is_array($item->$header)) {
                    Arr::set($itemData, $header, json_encode($item->$header));
                } else {
                    Arr::set($itemData, $header, $item->$header);
                }
            }

            $data->push($itemData);
        }

        return $data;
    }

    /**
     * Get export file name.
     *
     * @return string
     */
    private static function getFileName($export_type)
    {
        return sprintf('Export %s.%s', Carbon::now()->toDateTimeString(), $export_type);
    }
}
