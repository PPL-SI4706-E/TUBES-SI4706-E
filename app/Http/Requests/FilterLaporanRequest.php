<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterLaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $bulanAkhirRules = ['nullable', 'date_format:Y-m'];

        if ($this->filled('bulan_awal')) {
            $bulanAkhirRules[] = 'after_or_equal:bulan_awal';
        }

        return [
            'keyword' => ['nullable', 'string', 'max:100'],
            'status_bayar' => ['nullable', 'in:lunas,belum_lunas,menunggu_verifikasi'],
            'bulan_awal' => ['nullable', 'date_format:Y-m'],
            'bulan_akhir' => $bulanAkhirRules,
            'wilayah_id' => ['nullable', 'exists:wilayah,id'],
            'kategori_id' => ['nullable', 'exists:kategori_laporan,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bulan_akhir.after_or_equal' => 'Rentang bulan tidak valid.',
        ];
    }
}
