<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\GdprRequest;
use App\Models\ProductDownload;
use App\Services\Auth\SessionManager;
use App\Services\Download\DownloadTokenManager;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function transactions(Request $request)
    {
        return view('user.transactions', [
            'orders' => $request->user()->orders()->with(['items', 'refund'])->latest()->paginate(15),
        ]);
    }

    public function downloads(Request $request)
    {
        $productIds = $request->user()->memberAccess()->whereNull('revoked_at')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->pluck('product_id');
        return view('user.downloads', [
            'files' => ProductDownload::with('product')->whereIn('product_id', $productIds)->get(),
        ]);
    }

    /** Buat token download (15 menit) lalu redirect ke streaming. */
    public function requestDownload(Request $request, ProductDownload $file, DownloadTokenManager $tokens)
    {
        $token = $tokens->issue($request->user(), $file);
        return redirect()->route('user.download.serve', $token->token);
    }

    public function serveDownload(string $token, DownloadTokenManager $tokens)
    {
        return $tokens->serve($token);
    }

    public function certificateDownload(Request $request, Certificate $certificate)
    {
        abort_unless((int) $certificate->user_id === (int) $request->user()->id, 403);
        $path = storage_path('app/' . $certificate->pdf_path);
        if (! is_file($path)) {
            app(\App\Services\LMS\CertificateGenerator::class)->generateIfComplete($request->user(), $certificate->classRoom);
        }
        return response()->download($path, $certificate->code . '.pdf');
    }

    public function sessions(Request $request)
    {
        return view('user.sessions', [
            'sessions' => $request->user()->deviceSessions()->whereNull('revoked_at')->latest('last_active_at')->get(),
            'currentSessionId' => session()->getId(),
            'trustedDevices' => $request->user()->trustedDevices()->where('expires_at', '>', now())->latest()->get(),
        ]);
    }

    public function revokeTrustedDevice(Request $request, int $id)
    {
        app(\App\Services\Auth\TrustedDeviceManager::class)->revoke($request->user(), $id);
        return back()->with('status', 'Perangkat tepercaya dicabut. 2FA akan diminta lagi di perangkat itu.');
    }

    public function revokeSession(Request $request, int $id, SessionManager $sessions)
    {
        $sessions->revoke($request->user(), $id);
        return back()->with('status', 'Sesi berhasil dicabut.');
    }

    public function revokeOtherSessions(Request $request, SessionManager $sessions)
    {
        $sessions->revokeOthers($request->user());
        return back()->with('status', 'Semua sesi lain berhasil dicabut.');
    }

    public function gdpr(Request $request)
    {
        return view('user.gdpr', [
            'requests' => GdprRequest::where('user_id', $request->user()->id)->latest()->get(),
        ]);
    }

    public function gdprStore(Request $request)
    {
        $data = $request->validate(['type' => 'required|in:export,delete']);
        $existing = GdprRequest::where('user_id', $request->user()->id)->where('type', $data['type'])
            ->whereIn('status', ['requested', 'processing', 'ready'])->exists();
        if ($existing) return back()->withErrors(['type' => 'Permintaan serupa sedang diproses.']);

        $gdpr = GdprRequest::create([
            'user_id' => $request->user()->id,
            'type' => $data['type'],
            'cooling_until' => $data['type'] === 'delete' ? now()->addDays(30) : null,
        ]);

        if ($data['type'] === 'export') {
            app(\App\Services\GDPR\DataExporter::class)->process($gdpr); // dataset kecil: proses langsung
        }
        return back()->with('status', $data['type'] === 'export'
            ? 'Export siap. Link download dikirim ke email & tersedia di bawah (24 jam).'
            : 'Permintaan penghapusan dicatat. Masa tunggu 30 hari sebelum diproses admin.');
    }

    public function gdprDownload(Request $request, GdprRequest $gdpr)
    {
        abort_unless((int) $gdpr->user_id === (int) $request->user()->id, 403);
        abort_unless($gdpr->status === 'ready' && $gdpr->export_path, 404);
        abort_if($gdpr->export_expires_at?->isPast(), 410, 'Link export kedaluwarsa. Ajukan ulang.');
        return response()->download(storage_path('app/' . $gdpr->export_path));
    }
}
