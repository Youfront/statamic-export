<?php

namespace Youfront\Export;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Actions\Action;

class Export extends Action
{
    /**
     * Download items as JSON.
     *
     * @param $items
     * @param $values
     * @return false|Response
     */
    public function download($items, $values)
    {
        $data = $items->map(function($item) {
            return $item->data();
        });

        return response()
            ->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $this::getFileName() . '"');
    }

    public function authorize($user, $item)
    {
        return $user->can('view', $item);
    }

    /**
     * Get export file name.
     *
     * @return string
     */
    private static function getFileName()
    {
        return sprintf('Export %s.json', Carbon::now()->toDateTimeString());
    }
}
