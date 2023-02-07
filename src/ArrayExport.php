<?php

namespace Youfront\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ArrayExport implements FromArray, WithHeadings
{
    protected $headings;
    protected $entries;

    public function __construct(array $headings, array $entries)
    {
        $this->headings = $headings;
        $this->entries = $entries;
    }

    public function array(): array
    {
        return $this->entries;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
