<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminOwnerDocumentReviewRequest;
use App\Http\Resources\OwnerDocumentResource;
use App\Http\Resources\OwnerOwnerDocumentResource;
use App\Mail\OwnerDocumentMissingMail;
use App\Mail\OwnerDocumentReviewedMail;
use App\Models\Owner;
use App\Models\OwnerDocument;
use Illuminate\Support\Facades\Mail;
use App\Services\EncryptedStorage;
use App\Services\TelegramNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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


    public function notifyOwnerMissingDocuments(Request $request)
    {
        $request->validate([
            'owner_id' => ['required', 'exists:owners,id'],
            'type' => ['required', 'in:missing_documents,missing_logo,need_more_documents'],
            'message_text' => ['nullable', 'string', 'max:1000'],
        ]);

        $owner = Owner::with('user')->findOrFail((int) $request->owner_id);

        if (! $owner->user || ! $owner->user->email) {
            return response()->json([
                'success' => false,
                'message' => 'Owner email not found.',
            ], 404);
        }

        $hasDocuments = OwnerDocument::where('owner_id', $owner->id)->exists();

        // Example: adjust this if your owner/company logo is stored somewhere else
        $hasLogo = ! empty($owner->logo);

        $type = $request->type;
        $messageText = $request->message_text;

        if ($type === 'missing_documents') {
            if ($hasDocuments) {
                return response()->json([
                    'success' => false,
                    'message' => 'This owner already uploaded documents.',
                ], 400);
            }

            $messageText = $messageText ?: 'Our records show that you have not uploaded your verification documents yet. Please upload them as soon as possible.';
        }

        if ($type === 'missing_logo') {
            if ($hasLogo) {
                return response()->json([
                    'success' => false,
                    'message' => 'This owner already uploaded logo.',
                ], 400);
            }

            $messageText = $messageText ?: 'Our records show that your logo has not been uploaded yet. Please upload your logo as soon as possible.';
        }

        if ($type === 'need_more_documents') {
            $messageText = $messageText ?: 'Additional verification documents are required. Please log in and upload the requested documents as soon as possible.';
        }

        Mail::to($owner->user->email)->send(
            new OwnerDocumentMissingMail($owner->user, $messageText)
        );

        return response()->json([
            'success' => true,
            'message' => 'Reminder email sent to owner successfully.',
            'data' => [
                'owner_id' => $owner->id,
                'owner_email' => $owner->user->email,
                'type' => $type,
            ],
        ]);
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
    DB::transaction(function () use ($request, $ownerDocument) {
        $ownerDocument->status = $request->status;
        $ownerDocument->reviewed_by = $request->user()->id;
        $ownerDocument->reviewed_at = now();
        $ownerDocument->rejection_reason = $request->status === 'rejected'
            ? $request->rejection_reason
            : null;

        $ownerDocument->save();

        $ownerDocument->loadMissing('owner.user');

        // If approved, update related user role to owner
        if (
            $request->status === 'approved' &&
            $ownerDocument->owner?->user
        ) {
            $ownerDocument->owner->user->update([
                'role' => 'owner',
            ]);
        }
    });

    $ownerDocument->load('owner.user');

    // Send email to owner
    if ($ownerDocument->owner?->user?->email) {
        $messageText = match ($ownerDocument->status) {
            'approved' => 'Your verification document has been approved successfully.',
            'rejected' => 'Your verification document has been rejected. Reason: ' . ($ownerDocument->rejection_reason ?? 'No reason provided.'),
            default => 'Your verification document status has been updated.',
        };

        Mail::to($ownerDocument->owner->user->email)->send(
            new OwnerDocumentReviewedMail(
                $ownerDocument->owner->user,
                $ownerDocument,
                $messageText
            )
        );
    }

    return response()->json([
        'success' => true,
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