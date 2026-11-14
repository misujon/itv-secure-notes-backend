<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AggregationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class UserPostController extends Controller
{
    public function __construct(
        private readonly AggregationService $aggregationService,
    ) {}

    public function __invoke(Request $request, string $user): View
    {
        $page = max((int) $request->integer('page', 1), 1);
        $perPage = 15;

        $result = $this->aggregationService->getUserPosts($user, $page, $perPage);

        $paginator = new LengthAwarePaginator(
            $result['posts'],
            $result['meta']['total'],
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.users.posts', [
            'user' => $result['user'],
            'posts' => $paginator,
        ]);
    }
}
