<?php

namespace Youfront\Export;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\Writer;
use Statamic\Actions\Action;

class Export extends Action
{
    /**
     * Exclude statamic fields
     */
    const EXCLUDED_COLUMNS = [
        'blueprint',
        'updated_by',
    ];

    /**
     * Title
     */
    public static function title()
    {
        return __('CSV Export');
    }

    /**
     * Download items as CSV.
     *
     * @param $items
     * @param $values
     * @return false|Response
     * @throws CannotInsertRecord
     * @throws Exception
     */
    public function download($items, $values)
    {
        $headers = $this::getHeaders($items);
        $entries = $this::getEntries($headers, $items);

        $csv = Writer::createFromString('');
        $csv->insertOne($headers->toArray());
        $csv->insertAll($entries->toArray());

        return new Response($csv->toString(), 200, [
            'Content-Disposition' => 'attachment; filename="' . $this::getFileName() . '"',
        ]);
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
                if (in_array($key, static::EXCLUDED_COLUMNS)) {
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
    private static function getFileName()
    {
        return sprintf('Export %s.csv', Carbon::now()->toDateTimeString());
    }
}
