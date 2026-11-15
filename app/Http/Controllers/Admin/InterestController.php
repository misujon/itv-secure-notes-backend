<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AggregationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class InterestController extends Controller
{
    public function __construct(
        private readonly AggregationService $aggregationService,
    ) {}

    /**
     * Render users grouped by their profile interests via MongoDB aggregation.
     */
    public function __invoke(Request $request): View
    {
        $page = max((int) $request->integer('page', 1), 1);
        $perPage = 15;

        $result = $this->aggregationService->groupUsersByInterests($page, $perPage);

        $paginator = new LengthAwarePaginator(
            $result['data'],
            $result['meta']['total'],
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.interests.index', [
            'groups' => $paginator,
        ]);
    }
}
