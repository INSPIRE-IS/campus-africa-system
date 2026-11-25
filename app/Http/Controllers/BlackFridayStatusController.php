<?php

namespace App\Http\Controllers;

use App\Services\WordPressBookingFeePromotionService;
use Illuminate\Http\Request;

class BlackFridayStatusController extends Controller
{
    public function index(Request $request)
    {
        abort_unless((int) $request->user()->role_super === 1, 403);

        $manifestPath = public_path('server-admin-build/.vite/manifest.json');
        $manifest = is_readable($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : null;

        return response()->view('server-admin.black-friday', [
            'entry' => $manifest['black-friday.jsx'] ?? null,
        ])->header('Cache-Control', 'private, no-store');
    }

    public function status(Request $request, WordPressBookingFeePromotionService $promotion)
    {
        abort_unless((int) $request->user()->role_super === 1, 403);

        try {
            $data = $promotion->verification();
        } catch (\Exception $exception) {
            report($exception);

            return response()->json([
                'message' => 'Could not verify live WordPress fees. Check the application logs and retry.',
            ], 503)->header('Cache-Control', 'private, no-store');
        }

        return response()->json($data)->header('Cache-Control', 'private, no-store');
    }
}
