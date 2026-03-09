<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OwnerDocumentStoreRequest;
use App\Http\Requests\OwnerDocumentUpdateRequest;
use App\Http\Resources\OwnerDocumentResource;
use App\Http\Resources\OwnerOwnerDocumentResource;
use App\Models\Owner;
use App\Models\OwnerDocument;
use App\Services\EncryptedStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OwnerDocumentController extends Controller
{
    private function isAdmin(Request $request): bool
    {
        $user = $request->user();
        return $user && $user->role === 'admin'; // or $user?->isAdmin() ?? false
    }

    private function ownerId(Request $request): int
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthorized. Missing/invalid token.');
        }

        $owner = Owner::where('user_id', $user->id)->first();

        if (! $owner) {
            abort(404, 'Owner not found for this user. Create owner first.');
        }

        return $owner->id;
    }

    public function index(Request $request)
    {
        $isAdmin = $this->isAdmin($request);

        $query = OwnerDocument::query()
            ->with('owner')
            ->latest();

        // Owner: only own docs
        if (! $isAdmin) {
            $ownerId = $this->ownerId($request);
            $query->where('owner_id', $ownerId);
        }

        // Optional status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $paginated = $query->paginate(20);

        return $isAdmin
            ? OwnerDocumentResource::collection($paginated)
            : OwnerOwnerDocumentResource::collection($paginated);
    }

    public function store(OwnerDocumentStoreRequest $request)
    {
        $ownerId = $this->ownerId($request);

        $file = $request->file('file');
        if (! $file || ! $file->isValid()) {
            return response()->json([
                'message' => 'Invalid file upload.',
                'errors' => ['file' => [$file?->getErrorMessage() ?? 'File missing']],
            ], 422);
        }

        $disk = 'private';
        $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');

        // safer path
        $path = 'owner-documents/' . Str::uuid()->toString() . '.' . $ext . '.enc';

        try {
            $doc = DB::transaction(function () use ($request, $ownerId, $file, $disk, $path) {
                // Encrypt + store (note: file_get_contents loads file into RAM; stream is better if you implement it)
                EncryptedStorage::putEncrypted(
                    $disk,
                    $path,
                    file_get_contents($file->getRealPath())
                );

                return OwnerDocument::create([
                    'owner_id'       => $ownerId, // ✅ never from request
                    'document_type'  => $request->document_type,
                    'country'        => strtoupper($request->country),
                    'file_path'      => $path,
                    'disk'           => $disk,
                    'original_name'  => $file->getClientOriginalName(),
                    'mime_type'      => $file->getClientMimeType(),
                    'size'           => $file->getSize(),
                    'uploaded_at'    => now(),
                    'status'         => 'pending',
                ]);
            });

            $doc->load('owner');

            return (new OwnerOwnerDocumentResource($doc))
                ->response()
                ->setStatusCode(201);

        } catch (\Throwable $e) {
            // cleanup file if it exists
            try {
                Storage::disk($disk)->delete($path);
            } catch (\Throwable $cleanupEx) {
                // ignore
            }

            report($e);
            return response()->json(['message' => 'Failed to upload document.'], 500);
        }
    }

    public function show(Request $request, OwnerDocument $ownerDocument)
    {
        $isAdmin = $this->isAdmin($request);

        if (! $isAdmin) {
            $ownerId = $this->ownerId($request);
            abort_if($ownerDocument->owner_id !== $ownerId, 403, 'Forbidden');
            return new OwnerOwnerDocumentResource($ownerDocument->load('owner'));
        }

        return new OwnerDocumentResource($ownerDocument->load('owner'));
    }

    public function update(OwnerDocumentUpdateRequest $request, OwnerDocument $ownerDocument)
    {
        $isAdmin = $this->isAdmin($request);

        // Owner can only update own document
        if (! $isAdmin) {
            $ownerId = $this->ownerId($request);
            abort_if($ownerDocument->owner_id !== $ownerId, 403, 'Forbidden');
        }

        // Owner cannot set status via request (admin handles review)
        $ownerDocument->fill($request->only(['document_type', 'country']));

        if ($request->filled('country')) {
            $ownerDocument->country = strtoupper($request->country);
        }

        // Replace file (owner or admin) -> re-encrypt + replace
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            if (! $file || ! $file->isValid()) {
                throw ValidationException::withMessages([
                    'file' => [$file?->getErrorMessage() ?? 'The file failed to upload.'],
                ]);
            }

            $disk = 'private';
            $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $newPath = 'owner-documents/' . Str::uuid()->toString() . '.' . $ext . '.enc';

            try {
                DB::transaction(function () use ($request, $ownerDocument, $file, $disk, $newPath, $isAdmin) {
                    EncryptedStorage::putEncrypted(
                        $disk,
                        $newPath,
                        file_get_contents($file->getRealPath())
                    );

                    // delete old encrypted file
                    if ($ownerDocument->disk && $ownerDocument->file_path) {
                        Storage::disk($ownerDocument->disk)->delete($ownerDocument->file_path);
                    }

                    $ownerDocument->disk = $disk;
                    $ownerDocument->file_path = $newPath;
                    $ownerDocument->original_name = $file->getClientOriginalName();
                    $ownerDocument->mime_type = $file->getClientMimeType();
                    $ownerDocument->size = $file->getSize();

                    // When owner updates file -> reset status
                    if (! $isAdmin) {
                        $ownerDocument->status = 'pending';
                        $ownerDocument->reviewed_at = null;
                        $ownerDocument->reviewed_by = null;
                        $ownerDocument->rejection_reason = null;
                    }

                    // update other fields already filled
                    $ownerDocument->save();
                });

            } catch (\Throwable $e) {
                // cleanup new file if it exists
                try {
                    Storage::disk($disk)->delete($newPath);
                } catch (\Throwable $cleanupEx) {
                    // ignore
                }

                report($e);
                return response()->json(['message' => 'Failed to update document.'], 500);
            }
        } else {
            $ownerDocument->save();
        }

        $ownerDocument->load('owner');

        return $isAdmin
            ? new OwnerDocumentResource($ownerDocument)
            : new OwnerOwnerDocumentResource($ownerDocument);
    }

    public function destroy(Request $request, OwnerDocument $ownerDocument)
    {
        $isAdmin = $this->isAdmin($request);

        if (! $isAdmin) {
            $ownerId = $this->ownerId($request);
            abort_if($ownerDocument->owner_id !== $ownerId, 403, 'Forbidden');
        }

        if ($ownerDocument->disk && $ownerDocument->file_path) {
            Storage::disk($ownerDocument->disk)->delete($ownerDocument->file_path);
        }

        $ownerDocument->delete();

        return response()->json(['message' => 'Deleted']);
    }
}