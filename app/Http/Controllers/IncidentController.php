<?php

namespace App\Http\Controllers;

use App\Jobs\AI\AnalyzeIncidentImage;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class IncidentController extends Controller
{
    /**
     * Show the form for creating a new incident.
     */
    public function create()
    {
        return view('ai.upload');
    }

    /**
     * Store a newly created incident in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // Max 10MB
        ]);

        // Store the uploaded image
        $photoPath = $request->file('photo')->store('incidents', 'local');

        // Create the incident record
        $incident = Incident::create([
            'user_id' => Auth::id(),
            'photo_path' => $photoPath,
            'review_status' => 'PENDING_AI',
            // AI fields will be populated later by AI processing
        ]);

        // Dispatch Sewage Sentinel analysis job to run in background
        AnalyzeIncidentImage::dispatch($incident->id);

        return response()->json([
            'success' => true,
            'message' => 'Incident uploaded successfully. AI analysis is processing in the background.',
            'incident' => $incident,
        ], 201);
    }

    /**
     * Display a listing of incidents (for testing/viewing).
     */
    public function index()
    {
        $incidents = Incident::with('user')
            ->latest()
            ->get();

        return response()->json([
            'incidents' => $incidents,
        ]);
    }

    /**
     * Show admin review screen with incidents needing review.
     */
    public function review()
    {
        $incidents = Incident::with('user')
            ->where('review_status', 'NEEDS_REVIEW')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.review', compact('incidents'));
    }

    /**
     * Update incident review status (admin action).
     */
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

    /**
     * Show incidents dashboard with risk scores and priority.
     */
    public function dashboard()
    {
        $incidents = Incident::with('user')
            ->whereNotNull('ai_label')
            ->orderByRaw('CASE WHEN risk_score IS NOT NULL THEN risk_score ELSE 0 END DESC')
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by priority
        $highPriority = $incidents->where('risk_score', '>=', 70);
        $mediumPriority = $incidents->whereBetween('risk_score', [30, 69]);
        $lowPriority = $incidents->where('risk_score', '<', 30);

        return view('ai.incidents.dashboard', compact('incidents', 'highPriority', 'mediumPriority', 'lowPriority'));
    }

    /**
     * Get priority level based on risk score.
     */
    public static function getPriorityLevel(?int $riskScore): string
    {
        if ($riskScore === null) {
            return 'UNKNOWN';
        }

        if ($riskScore >= 70) {
            return 'HIGH';
        } elseif ($riskScore >= 30) {
            return 'MEDIUM';
        } else {
            return 'LOW';
        }
    }

    /**
     * Show resolved/completed incidents.
     */
    public function resolved()
    {
        $incidents = Incident::with('user')
            ->whereIn('review_status', ['SEWAGE_CONFIRMED', 'NOT_SEWAGE_CONFIRMED'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $sewageConfirmed = $incidents->where('review_status', 'SEWAGE_CONFIRMED');
        $notSewage = $incidents->where('review_status', 'NOT_SEWAGE_CONFIRMED');

        return view('ai.incidents.resolved', compact('incidents', 'sewageConfirmed', 'notSewage'));
    }

    /**
     * Serve incident image.
     */
    public function showImage(Incident $incident)
    {
        if (!Storage::disk('local')->exists($incident->photo_path)) {
            abort(404, 'Image not found');
        }

        $file = Storage::disk('local')->get($incident->photo_path);
        $mimeType = Storage::disk('local')->mimeType($incident->photo_path);

        return response($file, 200)->header('Content-Type', $mimeType);
    }
}
