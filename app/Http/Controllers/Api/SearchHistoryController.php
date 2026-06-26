<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\SearchHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchHistoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $history = SearchHistory::where('user_id', $request->user()->id)
            ->latest()
            ->limit(20)
            ->get();

        return $this->success($history);
    }

    public function destroy(Request $request, SearchHistory $searchHistory): JsonResponse
    {
        if ($searchHistory->user_id !== $request->user()->id) {
            return $this->error('Not found', 404);
        }

        $searchHistory->delete();

        return $this->success(null, 'Search history item deleted');
    }

    public function clear(Request $request): JsonResponse
    {
        SearchHistory::where('user_id', $request->user()->id)->delete();

        return $this->success(null, 'Search history cleared');
    }
}
