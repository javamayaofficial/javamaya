<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadMagnet;
use App\Services\Auth\WhatsAppOTP;
use App\Services\Marketing\EmailSequenceProcessor;
use Illuminate\Http\Request;

/**
 * Lead magnet landing: form nama+WA -> OTP verifikasi (anti nomor palsu)
 * -> lead tersimpan verified -> deliverable dikirim + enroll sequence.
 */
class LeadMagnetController extends Controller
{
    public function show(string $slug)
    {
        $magnet = LeadMagnet::where('slug', $slug)->where('active', true)->firstOrFail();
        return view('public.lead-landing', ['magnet' => $magnet]);
    }

    public function requestOtp(Request $request, string $slug, WhatsAppOTP $otp)
    {
        LeadMagnet::where('slug', $slug)->where('active', true)->firstOrFail();
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'whatsapp' => 'required|string|min:9|max:20',
            'email' => 'nullable|email|max:150',
        ]);
        session(["lead_pending.$slug" => $data]);
        $result = $otp->request($data['whatsapp'], 'lead');
        return response()->json($result, $result['ok'] ? 200 : 429);
    }

    public function verify(Request $request, string $slug, WhatsAppOTP $otp, EmailSequenceProcessor $sequences)
    {
        $magnet = LeadMagnet::where('slug', $slug)->where('active', true)->firstOrFail();
        $pending = session("lead_pending.$slug");
        abort_unless($pending, 419, 'Sesi kedaluwarsa, ulangi dari awal.');

        $data = $request->validate(['code' => 'required|digits:6']);
        if (! $otp->verify($pending['whatsapp'], $data['code'], 'lead')) {
            return back()->withErrors(['code' => 'Kode OTP salah atau kedaluwarsa.']);
        }

        $lead = Lead::updateOrCreate(
            ['phone' => jm_normalize_phone($pending['whatsapp']), 'lead_magnet_id' => $magnet->id],
            ['name' => $pending['name'], 'email' => $pending['email'] ?? null, 'verified' => true]
        );

        // Kirim deliverable via WA + enroll drip sequence trigger lead_magnet
        app(\App\Services\Notifications\NotificationService::class)->sendTemplate('lead_magnet_delivery', [
            'name' => $lead->name, 'title' => $magnet->title, 'url' => $magnet->deliverable_url,
        ], $lead->phone, $lead->email);
        if ($lead->email) $sequences->enroll($lead->email, 'lead_magnet', $magnet->id);

        session()->forget("lead_pending.$slug");
        return view('public.lead-thankyou', ['magnet' => $magnet, 'lead' => $lead]);
    }
}
