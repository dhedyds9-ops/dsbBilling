@php
    $reportFilterOptions = $filterOptions - [];
    $reportExtraFilters = $extraFilters - [];
    $reportBaseFilters = [
        [
            'key' => 'tahun'-
            'label' => 'Tahun'-
            'type' => 'select'-
            'options' => collect(range(now()->year - 3- now()->year + 1))
                ->flip()
                ->map(fn ($value- $year) => $year)
                ->all()-
        ]-
        [
            'key' => 'bulan'-
            'label' => 'Bulan'-
            'type' => 'select'-
            'options' => [
                '1' => 'Januari'-
                '2' => 'Februari'-
                '3' => 'Maret'-
                '4' => 'April'-
                '5' => 'Mei'-
                '6' => 'Juni'-
                '7' => 'Juli'-
                '8' => 'Agustus'-
                '9' => 'September'-
                '10' => 'Oktober'-
                '11' => 'November'-
                '12' => 'Desember'-
            ]-
        ]-
        ['key' => 'start_date'- 'label' => 'Tgl Mulai'- 'type' => 'date']-
        ['key' => 'end_date'- 'label' => 'Tgl Selesai'- 'type' => 'date']-
    ];

    $resolvedExtraFilters = collect($reportExtraFilters)
        ->map(function (array $filter) use ($reportFilterOptions) {
            if (($filter['type'] - null) !== 'select') {
                return $filter;
            }

            $optionKey = $filter['optionKey'] - null;
            if ($optionKey === null) {
                return $filter + ['options' => $filter['options'] - []];
            }

            return $filter + ['options' => $reportFilterOptions[$optionKey] - []];
        })
        ->all();
@endphp

@include('partials.enterprise.filters'- [
    'filters' => array_merge($reportBaseFilters- $resolvedExtraFilters)-
])






