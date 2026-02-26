<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminOwnerDocumentReviewRequest;
use App\Http\Resources\OwnerDocumentResource;
use App\Http\Resources\OwnerOwnerDocumentResource;
use App\Models\OwnerDocument;
use App\Services\EncryptedStorage;
use App\Services\TelegramNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

class AdminOwnerDocumentController extends Controller
{
    /**
     * Optional: clear OTP fields if expired (auto-delete behavior)
     */
    private function clearOtpIfExpired(OwnerDocument $doc): void
    {
        if ($doc->otp_expires_at && now()->greaterThan($doc->otp_expires_at)) {
            $doc->forceFill([
                'otp_hash' => null,
                'otp_expires_at' => null,
                'otp_attempts' => 0,
                'otp_verified_at' => null,
            ])->save();
        }
    }
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user?->role === 'admin'; // or $user?->isAdmin()
    
        $query = OwnerDocument::query()
            ->with('owner') // only if you need owner in response
            ->latest();
    
        // filter by status (admin can filter all; owner can filter theirs)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
         // ✅ Admin can filter by owner_id via ?owner_id=7
        if ($isAdmin && $request->filled('owner_id')) {
            $query->where('owner_id', (int) $request->owner_id);
        }

    
        // IMPORTANT: if not admin, only show documents belonging to this user
        if (! $isAdmin) {
            $query->whereHas('owner', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
    
        $paginated = $query->paginate(20);
    
        return $isAdmin
            ? OwnerDocumentResource::collection($paginated)
            : OwnerOwnerDocumentResource::collection($paginated);
    }

    public function store(Request $request)
    {
        $request->validate([
            'owner_id' => ['required', 'exists:owners,id'],
            'document_type' => ['required', 'string'],
            'country' => ['required', 'string', 'size:2'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $file = $request->file('file');

        $disk = 'private';
        $ext = $file->getClientOriginalExtension() ?: ($file->extension() ?: 'bin');
        $path = 'owner-documents/' . uniqid('doc_') . '.' . $ext . '.enc';

        EncryptedStorage::putEncrypted($disk, $path, file_get_contents($file->getRealPath()));

        $doc = OwnerDocument::create([
            'owner_id' => (int) $request->owner_id,
            'document_type' => $request->document_type,
            'country' => strtoupper($request->country),
            'file_path' => $path,
            'disk' => $disk,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_at' => now(),
            'status' => 'pending',
        ]);

        return (new OwnerDocumentResource($doc))
            ->response()
            ->setStatusCode(201);
    }

    public function sendOtp(Request $request, OwnerDocument $ownerDocument)
    {
        // ✅ auto-clear if old OTP expired
        $this->clearOtpIfExpired($ownerDocument);

        // ✅ throttle 30 seconds (safe + simple)
        if ($ownerDocument->otp_last_sent_at && $ownerDocument->otp_last_sent_at->gt(now()->subSeconds(30))) {
            return response()->json(['message' => 'Please wait before requesting OTP again.'], 429);
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $ownerDocument->forceFill([
            'otp_hash' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(5),
            'otp_attempts' => 0,
            'otp_verified_at' => null,
            'otp_last_sent_at' => now(),
        ])->save();

        // ✅ URL for Telegram inline button
        // Set APP_FRONTEND_URL in .env like: https://your-frontend.com
        $frontendBase = rtrim(config('app.frontend_url') ?? env('APP_FRONTEND_URL', ''), '/');
        $frontendVerifyUrl = $frontendBase
            ? "{$frontendBase}/admin/owner-documents/{$ownerDocument->id}?action=verify-otp"
            : null;

        try {
            $message = "🔐 <b>OTP for OwnerDocument #{$ownerDocument->id}</b>\n\n"
                . "<code>{$otp}</code>\n\n"
                . "⏱ Expires: 5 minutes";

            $replyMarkup = null;

            if ($frontendVerifyUrl) {
                $replyMarkup = [
                    'inline_keyboard' => [
                        [
                            ['text' => '✅ Verify OTP', 'url' => $frontendVerifyUrl],
                        ],
                    ],
                ];
            }

            TelegramNotifier::send($message, 'HTML', $replyMarkup);
        } catch (\Throwable $e) {
            \Log::error('Telegram send failed', [
                'doc_id' => $ownerDocument->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Telegram send failed',
                'error' => $e->getMessage(),
                'data' => new OwnerDocumentResource($ownerDocument->fresh()),
            ], 500);
        }

        // ✅ return copy text so frontend can copy instantly
        $copyText = "🔐 OTP for OwnerDocument #{$ownerDocument->id}\nCode: {$otp}\nExpires: 5 minutes";

        return response()->json([
            'message' => 'OTP sent to Telegram.',
            'copy_text' => $copyText,
            'data' => new OwnerDocumentResource($ownerDocument->fresh()),
        ]);
    }

    public function verifyOtp(Request $request, OwnerDocument $ownerDocument)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        // ✅ auto-clear if expired
        $this->clearOtpIfExpired($ownerDocument);

        // missing/expired
        if (!$ownerDocument->otp_hash || !$ownerDocument->otp_expires_at) {
            return response()->json(['message' => 'OTP expired. Request a new one.'], 400);
        }

        // too many attempts
        if ($ownerDocument->otp_attempts >= 5) {
            return response()->json(['message' => 'Too many attempts. Request a new OTP.'], 429);
        }

        $ok = Hash::check($request->otp, $ownerDocument->otp_hash);

        if (!$ok) {
            $ownerDocument->increment('otp_attempts');
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        // ✅ success: mark verified + clear OTP to prevent reuse
        $ownerDocument->forceFill([
            'otp_verified_at' => now(),
            'otp_attempts' => 0,
            'otp_hash' => null,
            'otp_expires_at' => null,
        ])->save();

        // ✅ signed download URL (10 minutes)
        $url = URL::temporarySignedRoute(
            'admin.owner-documents.download',
            now()->addMinutes(10),
            ['ownerDocument' => $ownerDocument->id]
        );

        return response()->json([
            'download_url' => $url,
            'data' => new OwnerDocumentResource($ownerDocument->fresh()),
        ]);
    }

    public function review(AdminOwnerDocumentReviewRequest $request, OwnerDocument $ownerDocument)
    {
        $ownerDocument->status = $request->status;

        $ownerDocument->reviewed_by = $request->user()->id;
        $ownerDocument->reviewed_at = now();
        $ownerDocument->rejection_reason = $request->status === 'rejected'
            ? $request->rejection_reason
            : null;

        $ownerDocument->save();

        return response()->json([
            'message' => 'Document reviewed successfully',
            'data' => new OwnerDocumentResource($ownerDocument),
        ]);
    }

    public function download(Request $request, OwnerDocument $ownerDocument)
    {
        /**
         * ✅ Recommended:
         * Put this route behind ->middleware('signed')
         * Then you DON'T need otp_verified_at check here.
         *
         * Route example:
         * Route::get('/admin/owner-documents/{ownerDocument}/download', ...)
         *   ->name('admin.owner-documents.download')
         *   ->middleware(['signed']);
         */

        // ✅ Telegram alert when document viewed/downloaded
        try {
            $adminId = optional($request->user())->id ?: 'guest';
            TelegramNotifier::send(
                "👁️ <b>Document viewed</b>\nDoc: #{$ownerDocument->id}\nOwner: #{$ownerDocument->owner_id}\nBy admin: <code>{$adminId}</code>\nTime: <code>" . now()->toDateTimeString() . "</code>",
                'HTML'
            );
        } catch (\Throwable $e) {
            // don't block download if telegram fails
            \Log::warning('Telegram view alert failed', ['doc_id' => $ownerDocument->id, 'error' => $e->getMessage()]);
        }

        if (!EncryptedStorage::exists($ownerDocument->disk, $ownerDocument->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $raw = EncryptedStorage::getDecrypted($ownerDocument->disk, $ownerDocument->file_path);

        return response($raw, 200, [
            'Content-Type' => $ownerDocument->mime_type ?? 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $ownerDocument->original_name . '"',
        ]);
    }
}