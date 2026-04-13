<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    /**
     * Ambil list aktivitas untuk navbar/dropdown.
     * Default: 5 terakhir, maksimum 20.
     */
    public function navbar(Request $request)
    {
        $limit = (int) $request->get('limit', 5);
        $limit = $limit > 0 ? min($limit, 20) : 5;

        $userId = Auth::id();

        $activities = Activity::query()
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->latest()
            ->take($limit)
            ->get();

        if ($request->wantsJson()) {
            return response()->json($activities);
        }

        return view('partials.activity_dropdown', compact('activities'));
    }

    /**
     * Halaman daftar aktivitas.
     */
    public function index()
    {
        $userId = Auth::id();

        $activities = Activity::query()
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->latest()
            ->paginate(15);

        return view('activity.index', compact('activities'));
    }

    /**
     * Hapus semua aktivitas user yang sedang login.
     */
    public function clear(Request $request)
    {
        $userId = Auth::id();

        Activity::query()
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Aktivitas berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Aktivitas berhasil dihapus.');
    }
}
