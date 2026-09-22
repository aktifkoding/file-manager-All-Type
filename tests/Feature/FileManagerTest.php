<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileManagerTest extends TestCase
{
    public function test_guest_diarahkan_ke_login(): void
    {
        $this->get('/files')->assertRedirect(route('login'));
    }

    public function test_user_bisa_login_dan_lihat_daftar_file(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.attempt'), [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('files.index'));
        $this->assertAuthenticatedAs($user);

        $this->get(route('files.index'))->assertOk();
    }

    public function test_upload_multi_file_tersimpan(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Dokumen']);

        $response = $this->actingAs($user)->post(route('files.store'), [
            'category_id' => $category->id,
            'files'       => [
                UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->image('foto.jpg'),
            ],
        ]);

        $response->assertRedirect(route('files.index'))->with('success');

        $this->assertSame(2, File::owned($user->id)->count());
        Storage::disk('local')->assertExists(File::first()->path);
    }

    public function test_user_tidak_bisa_akses_file_milik_user_lain(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $category = Category::create(['name' => 'Gambar']);

        $file = File::create([
            'user_id'       => $owner->id,
            'category_id'   => $category->id,
            'original_name' => 'rahasia.png',
            'path'          => UploadedFile::fake()->image('rahasia.png')->store('uploads'),
            'mime_type'     => 'image/png',
            'size'          => 1234,
        ]);

        $this->actingAs($intruder)->get(route('files.show', $file))->assertForbidden();
        $this->actingAs($intruder)->get(route('files.download', $file))->assertForbidden();
        $this->actingAs($intruder)->delete(route('files.destroy', $file))->assertForbidden();
    }

    public function test_hapus_ke_trash_lalu_restore_dan_hapus_permanen(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Lainnya']);

        $storedPath = UploadedFile::fake()->create('arsip.zip', 50)->store('uploads');
        $file = File::create([
            'user_id'       => $user->id,
            'category_id'   => $category->id,
            'original_name' => 'arsip.zip',
            'path'          => $storedPath,
            'mime_type'     => 'application/zip',
            'size'          => 5000,
        ]);

        // Soft delete → masuk trash
        $this->actingAs($user)->delete(route('files.destroy', $file))->assertRedirect();
        $this->assertSoftDeleted('files', ['id' => $file->id]);
        $this->assertSame(1, File::owned($user->id)->onlyTrashed()->count());

        // Restore
        $this->actingAs($user)->post(route('files.restore', $file->id))->assertRedirect();
        $this->assertSame(1, File::owned($user->id)->count());

        // Force delete → file fisik ikut terhapus
        $this->actingAs($user)->delete(route('files.force-delete', $file->id))->assertRedirect();
        $this->assertSame(0, File::owned($user->id)->withTrashed()->count());
        Storage::disk('local')->assertMissing($storedPath);
    }

    public function test_bulk_hapus_dan_zip_download(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Dokumen']);

        $f1 = File::create([
            'user_id' => $user->id, 'category_id' => $category->id,
            'original_name' => 'a.pdf', 'path' => UploadedFile::fake()->create('a.pdf', 10)->store('uploads'),
            'mime_type' => 'application/pdf', 'size' => 100,
        ]);
        $f2 = File::create([
            'user_id' => $user->id, 'category_id' => $category->id,
            'original_name' => 'b.pdf', 'path' => UploadedFile::fake()->create('b.pdf', 10)->store('uploads'),
            'mime_type' => 'application/pdf', 'size' => 200,
        ]);

        // Bulk download → response ZIP
        $this->actingAs($user)->post(route('files.bulk'), [
            'action' => 'download',
            'ids'    => [$f1->id, $f2->id],
        ])->assertDownload();

        // Bulk delete → masuk trash semua
        $this->actingAs($user)->post(route('files.bulk'), [
            'action' => 'delete',
            'ids'    => [$f1->id, $f2->id],
        ])->assertRedirect();

        $this->assertSame(2, File::owned($user->id)->onlyTrashed()->count());
    }

    public function test_export_csv_mengalirkan_file(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Dokumen']);
        File::create([
            'user_id' => $user->id, 'category_id' => $category->id,
            'original_name' => 'test.pdf', 'path' => 'uploads/test.pdf',
            'mime_type' => 'application/pdf', 'size' => 300,
        ]);

        $response = $this->actingAs($user)->get(route('files.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('test.pdf', $response->streamedContent());
    }

    public function test_api_token_dan_endpoint_files(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Gambar']);

        // 1. Minta token
        $tokenResponse = $this->postJson('/api/auth/token', [
            'email'    => $user->email,
            'password' => 'password',
            'device'   => 'test',
        ]);
        $tokenResponse->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
        $token = $tokenResponse->json('token');

        // 2. Upload via API
        $upload = $this->withToken($token)->postJson('/api/files', [
            'category_id' => $category->id,
            'files'       => [UploadedFile::fake()->image('api.png')],
        ]);
        $upload->assertCreated()->assertJsonStructure(['message', 'data' => [['id', 'original_name', 'human_size']]]);

        // 3. List via API
        $list = $this->withToken($token)->getJson('/api/files');
        $list->assertOk()->assertJsonCount(1, 'data.data');

        // 4. Tanpa token → 401
        $this->getJson('/api/files')->assertUnauthorized();
    }
}
