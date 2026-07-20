<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Material;
use App\Models\MaterialCompletion;
use App\Models\MemberAccess;
use App\Services\LMS\CertificateGenerator;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    protected function assertAccess(Request $request, ClassRoom $class): void
    {
        $hasAccess = MemberAccess::where('user_id', $request->user()->id)
            ->whereHas('product', fn ($q) => $q->where('class_id', $class->id))
            ->whereNull('revoked_at')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
        abort_unless($hasAccess, 403, 'Akses kelas Anda tidak aktif. Silakan perpanjang.');
    }

    public function show(Request $request, string $slug)
    {
        $class = ClassRoom::with('materials')->where('slug', $slug)->firstOrFail();
        $this->assertAccess($request, $class);

        $completedIds = MaterialCompletion::where('user_id', $request->user()->id)
            ->whereIn('material_id', $class->materials->pluck('id'))->pluck('material_id')->all();

        $expiry = MemberAccess::where('user_id', $request->user()->id)
            ->whereHas('product', fn ($q) => $q->where('class_id', $class->id))
            ->whereNull('revoked_at')->orderByDesc('id')->value('expires_at');

        return view('user.class-detail', [
            'class' => $class,
            'completedIds' => $completedIds,
            'progress' => $class->progressFor($request->user()),
            'certificate' => $class->certificates()->where('user_id', $request->user()->id)->first(),
            'expiresAt' => $expiry,
        ]);
    }

    /** Toggle materi selesai; progress 100% -> certificate auto-generate (idempotent). */
    public function toggleComplete(Request $request, Material $material, CertificateGenerator $certificates)
    {
        $class = $material->classRoom;
        $this->assertAccess($request, $class);

        $existing = MaterialCompletion::where('user_id', $request->user()->id)
            ->where('material_id', $material->id)->first();
        $existing ? $existing->delete() : MaterialCompletion::create([
            'user_id' => $request->user()->id, 'material_id' => $material->id, 'completed_at' => now(),
        ]);

        $certificate = $certificates->generateIfComplete($request->user(), $class);

        return response()->json([
            'ok' => true,
            'progress' => $class->progressFor($request->user()),
            'certificate_url' => $certificate ? route('user.certificate.download', $certificate->id) : null,
        ]);
    }
}
