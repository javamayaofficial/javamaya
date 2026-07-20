<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'duitku', 'name' => 'Duitku (VA, QRIS, e-wallet, retail)', 'sort_order' => 1],
            ['code' => 'xendit', 'name' => 'Xendit (VA, QRIS, e-wallet, kartu)', 'sort_order' => 2],
            ['code' => 'moota', 'name' => 'Transfer Bank (konfirmasi otomatis via Moota)', 'sort_order' => 3],
            ['code' => 'manual_transfer', 'name' => 'Transfer Bank (konfirmasi manual)', 'sort_order' => 4],
            ['code' => 'qris_manual', 'name' => 'QRIS (konfirmasi manual)', 'sort_order' => 5],
        ];
        foreach ($rows as $row) {
            PaymentGateway::firstOrCreate(['code' => $row['code']], $row + ['active' => false, 'sandbox_mode' => true]);
        }
    }
}
