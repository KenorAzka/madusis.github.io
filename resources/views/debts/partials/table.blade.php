@if($debts->count() > 0)
    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
        <thead>
            <tr style="border-bottom: 2px solid #e2e8f0; text-align: left; color: #64748b;">
                <th style="padding: 8px;">Pelanggan</th>
                <th style="padding: 8px;">Total Bon</th>
                <th style="padding: 8px;">Sisa Utang</th>
                <th style="padding: 8px;">Status</th>
                <th style="padding: 8px; text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($debts as $debt)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; font-weight: 600; color: #1e293b;">
                        {{ $debt->customer->name ?? 'Pelanggan Umum' }}
                    </td>
                    <td style="padding: 10px; color: #475569;">
                        Rp {{ number_format($debt->total_debt, 0, ',', '.') }}
                    </td>
                    <td style="padding: 10px; font-weight: 700; color: #ef4444;">
                        Rp {{ number_format($debt->remaining_debt, 0, ',', '.') }}
                    </td>
                    <td style="padding: 10px;">
                        @if($debt->status == 'unpaid')
                            <span style="background: #fee2e2; color: #dc2626; padding: 3px 8px; border-radius: 6px; font-size: 10px; font-weight: 700;">Belum Bayar</span>
                        @elseif($debt->status == 'partial')
                            <span style="background: #fef9c3; color: #ca8a04; padding: 3px 8px; border-radius: 6px; font-size: 10px; font-weight: 700;">Dicicil</span>
                        @else
                            <span style="background: #dcfce7; color: #16a34a; padding: 3px 8px; border-radius: 6px; font-size: 10px; font-weight: 700;">Lunas</span>
                        @endif
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        @if($debt->status != 'paid')
                            <form action="{{ route('debts.pay', $debt->id) }}" method="POST" style="display: inline-flex; gap: 4px; align-items: center;">
                                @csrf
                                <input type="number" name="amount_paid" placeholder="Nominal" max="{{ $debt->remaining_debt }}" min="1" style="width: 90px; padding: 4px 6px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 6px;" required>
                                <button type="submit" style="background: #1c2a3a; color: #fff; border: none; padding: 5px 10px; border-radius: 6px; font-size: 11px; cursor: pointer; font-weight: 600;">Bayar</button>
                            </form>
                        @else
                            <span style="color: #94a3b8; font-style: italic; font-size: 11px;">Selesai</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div style="text-align: center; color: #94a3b8; padding: 30px; font-size: 12px;">
        <i class="fa-solid fa-folder-open" style="font-size: 24px; margin-bottom: 8px; display: block; color: #cbd5e1;"></i>
        Tidak ada data utang ditemukan.
    </div>
@endif