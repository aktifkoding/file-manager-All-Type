<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class FileApiController extends Controller
{
    private const SORTABLE_COLUMNS = ['original_name', 'created_at', 'size'];
    private const ALLOWED_EXTENSIONS = 'jpg,jpeg,png,gif,webp,pdf,mp4,webm,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip';

    /** POST /api/auth/token — tukar email+password dengan API token. */
    public function token(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
            'device'   => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', strtolower($request->email))->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 422);
        }

        $token = $user->createToken($request->input('device', 'api'));

        return response()->json([
            'token' => $token->plainTextToken,
            'user'  => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ]);
    }

    /** GET /api/files — daftar file milik user. */
    public function index(Request $request)
    {
        $query = File::owned($request->user()->id)->with('category:id,name');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('name')) {
            $query->where('original_name', 'like', '%' . $request->name . '%');
        }
        if (in_array($request->sort_by, self::SORTABLE_COLUMNS, true)) {
            $query->orderBy($request->sort_by, $request->direction === 'desc' ? 'desc' : 'asc');
        } else {
            $query->latest();
        }

        $files = $query->paginate(min((int) $request->input('per_page', 15), 100));

        return response()->json(['data' => $files]);
    }

    /** POST /api/files — upload multipart (field: files[], category_id). */
    public function store(Request $request)
    {
        $request->validate([
            'files'       => ['required', 'array', 'min:1', 'max:10'],
            'files.*'     => ['required', 'file', 'max:5000', 'mimes:' . self::ALLOWED_EXTENSIONS],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $created = collect();

        foreach ($request->file('files') as $uploaded) {
            $path = $uploaded->store('uploads');

            $created->push(File::create([
                'user_id'       => $request->user()->id,
                'category_id'   => $request->category_id,
                'original_name' => $uploaded->getClientOriginalName(),
                'path'          => $path,
                'mime_type'     => $uploaded->getMimeType(),
                'size'          => $uploaded->getSize(),
            ]));
        }

        return response()->json([
            'message' => $created->count() . ' file berhasil di-upload.',
            'data'    => $created->map(fn (File $f) => $this->serialize($f)),
        ], 201);
    }

    /** GET /api/files/{file} */
    public function show(Request $request, File $file)
    {
        $this->authorizeOwner($request, $file);

        return response()->json(['data' => $this->serialize($file->load('category:id,name'))]);
    }

    /** DELETE /api/files/{file} — soft delete ke trash. */
    public function destroy(Request $request, File $file)
    {
        $this->authorizeOwner($request, $file);
        $file->delete();

        return response()->json(['message' => 'File dipindahkan ke trash.']);
    }

    /** GET /api/files/{file}/download */
    public function download(Request $request, File $file)
    {
        $this->authorizeOwner($request, $file);
        abort_unless(Storage::exists($file->path), 404, 'File tidak ditemukan.');

        return $file->download();
    }

    private function authorizeOwner(Request $request, File $file): void
    {
        abort_unless($file->user_id === $request->user()->id, 403, 'Tidak ada akses ke file ini.');
    }

    private function serialize(File $f): array
    {
        return [
            'id'            => $f->id,
            'original_name' => $f->original_name,
            'extension'     => $f->extension,
            'mime_type'     => $f->mime_type,
            'size'          => $f->size,
            'human_size'    => $f->human_size,
            'category'      => $f->category->name ?? null,
            'created_at'    => $f->created_at->toIso8601String(),
        ];
    }
}
