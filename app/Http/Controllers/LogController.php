<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use OpenApi\Annotations as OA;

class LogController extends Controller
{
    /**
     * @OA\Info(
     *    title="Ace Taxi Logger",
     *    version="1.0.0",
     * )
     */
    public function index(Request $request)
    {
        $query = Log::query();

        // Filter by log type
        if ($request->has('type') && in_array($request->type, ['info', 'error', 'warn'])) {
            $query->where('type', $request->type);
        }

        // Search by message
        if ($request->has('search') && !empty($request->search)) {
            $query->where('message', 'like', '%' . $request->search . '%');
        }

        // Sort by timestamp descending and paginate
        $logs = $query->orderBy('timestamp', 'desc')->paginate(10);

        return view('welcome', ['data' => $logs]);
    }

    /**
     * Store a new log entry.
     *
     * @OA\Post(
     *     path="/api/logs",
     *     summary="Create a new log entry",
     *     tags={"Logs"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", enum={"info", "error", "warn"}, example="error", description="Type of the log"),
     *             @OA\Property(property="message", type="string", example="Failed to connect to server: java.net.ConnectException: Connection refused", description="Log message or stack trace"),
     *             @OA\Property(property="source", type="string", nullable=true, example="NetworkService", description="Source component of the log")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Log created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Log created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time"),
     *                 @OA\Property(property="type", type="string", enum={"info", "error", "warn"}),
     *                 @OA\Property(property="message", type="string"),
     *                 @OA\Property(property="source", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="timestamp", type="array", @OA\Items(type="string", example="The timestamp field is required.")),
     *                 @OA\Property(property="type", type="array", @OA\Items(type="string", example="The type field must be one of info, error, warn.")),
     *                 @OA\Property(property="message", type="array", @OA\Items(type="string", example="The message field is required."))
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:info,error,warn',
                'message' => 'required|string',
                'source' => 'nullable|string|max:255',
                'timestamp' => 'nullable|integer',
            ]);

            // Log request time for debugging
            Log::info('Request received at: ' . Carbon::now()->toDateTimeString());

            // If timestamp is not provided, set it to the current timestamp
            if (!isset($validated['timestamp'])) {
                $now = Carbon::now('Europe/London');
                $validated['timestamp'] = $now->timestamp;
                Log::info('Generated timestamp: ' . $now->timestamp . ', Human-readable: ' . $now->toDateTimeString());
            } else {
                Log::info('Provided timestamp: ' . $validated['timestamp']);
            }

            $log = Log::create($validated);

            return Response::json([
                'message' => 'Log created successfully',
                'data' => $log,
                'generated_timestamp' => $validated['timestamp'],
                'human_readable_time' => Carbon::createFromTimestamp($validated['timestamp'], 'Europe/London')->toDateTimeString(),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating log: ' . $e->getMessage());
            return Response::json([
                'message' => 'Error creating log',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
