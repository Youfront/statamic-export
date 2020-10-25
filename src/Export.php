<?php

namespace Youfront\Export;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use League\Csv\Writer;
use Statamic\Actions\Action;

class Export extends Action
{
    /**
     * Download items as CSV.
     *
     * @param $items
     * @param $values
     * @return false|Response
     * @throws \League\Csv\CannotInsertRecord
     */
    public function download($items, $values)
    {
        $headers = $this::getHeaders($items);
        $entries = $this::getEntries($headers, $items);

        $csv = Writer::createFromString('');
        $csv->insertOne($headers->toArray());
        $csv->insertAll($entries->toArray());

        return new Response($csv->getContent(), 200, [
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

        foreach ($items as $item) {
            foreach ($item->data()->keys() as $key) {
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
                Arr::set($itemData, $header, Arr::get($item->data(), $header));
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
