<?php

namespace App\Http\Controllers;

use App\Jobs\AI\AnalyzeIncidentImage;
use App\Models\Incident;
use App\Services\Sns\SnsNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IncidentController extends Controller
{
    public function create()
    {
        return view('ai.upload');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $photoPath = $request->file('photo')->store('incidents', 'local');

        $incident = Incident::create([
            'user_id' => Auth::id() ?? null,
            'photo_path' => $photoPath,
            'review_status' => 'PENDING_AI',
        ]);

        AnalyzeIncidentImage::dispatch($incident->id);

        return response()->json([
            'success' => true,
            'message' => 'Incident uploaded successfully. AI is checking drainage relevance in the background.',
            'incident' => $incident,
        ], 201);
    }

    public function index()
    {
        $incidents = Incident::with('user')->latest()->get();

        return response()->json(['incidents' => $incidents]);
    }

    public function review()
    {
        $incidents = Incident::with('user')
            ->where('review_status', 'NEEDS_REVIEW')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('r_admin.review', compact('incidents'));
    }

    /**
     * Send incident to operations (with optional admin message).
     */
    public function sendToOperations(Request $request, Incident $incident, SnsNotifier $snsNotifier)
    {
        $validated = $request->validate([
            'admin_message' => ['nullable', 'string', 'max:2000'],
        ]);

        $incident->update([
            'admin_message' => $validated['admin_message'] ?? null,
            'sent_to_operations_at' => now(),
            'review_status' => 'SENT_TO_OPERATIONS',
        ]);

        $snsNotifier->incidentSentToOperations($incident->fresh());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Incident sent to operations.',
                'incident' => $incident->fresh(),
            ]);
        }

        return redirect()->route('admin.incidents.review')->with('success', 'Incident sent to operations.');
    }

    /**
     * Delete (soft delete) incident, with optional admin message.
     */
    public function destroy(Request $request, Incident $incident)
    {
        $validated = $request->validate([
            'admin_message' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! empty($validated['admin_message'])) {
            $incident->update(['admin_message' => $validated['admin_message']]);
        }
        $incident->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Incident deleted.',
            ]);
        }

        return redirect()->route('admin.incidents.review')->with('success', 'Incident deleted.');
    }

    public function updateReview(Request $request, Incident $incident)
    {
        $validated = $request->validate([
            'action' => 'required|in:confirm_sewage,reject_sewage',
        ]);

        $incident->update([
            'review_status' => $validated['action'] === 'confirm_sewage'
                ? 'SEWAGE_CONFIRMED'
                : 'NOT_SEWAGE_CONFIRMED',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Incident review status updated successfully',
            'incident' => $incident->fresh(),
        ]);
    }

    public function dashboard()
    {
        $incidents = Incident::with('user')
            ->orderByRaw('CASE WHEN ai_label IS NULL THEN 0 ELSE 1 END')
            ->orderByRaw('CASE WHEN risk_score IS NOT NULL THEN risk_score ELSE 0 END DESC')
            ->orderBy('created_at', 'desc')
            ->get();

        $analyzed = $incidents->filter(fn (Incident $i) => $i->ai_label !== null);

        $highPriority = $analyzed->where('risk_score', '>=', 70);
        $mediumPriority = $analyzed->whereBetween('risk_score', [30, 69]);
        $lowPriority = $analyzed->filter(fn (Incident $i) => $i->risk_score !== null && $i->risk_score < 30);

        return view('ai.incidents.dashboard', compact(
            'incidents',
            'highPriority',
            'mediumPriority',
            'lowPriority',
        ));
    }

    public static function getPriorityLevel(?int $riskScore): string
    {
        if ($riskScore === null) {
            return 'UNKNOWN';
        }
        if ($riskScore >= 70) {
            return 'HIGH';
        }
        if ($riskScore >= 30) {
            return 'MEDIUM';
        }

        return 'LOW';
    }

    public function showImage(Incident $incident)
    {
        if (! Storage::disk('local')->exists($incident->photo_path)) {
            abort(404, 'Image not found');
        }

        $file = Storage::disk('local')->get($incident->photo_path);
        $mimeType = Storage::disk('local')->mimeType($incident->photo_path);

        return response($file, 200)->header('Content-Type', $mimeType);
    }
}
