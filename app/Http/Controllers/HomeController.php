<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class HomeController extends Controller
{
    /**
     * Menampilkan halaman katalog buku dengan fitur pencarian, filter, sorting, dan paginasi.
     */
    public function index(Request $request)
    {
        // TODO 3: Lengkapi logika index():

        // 1. Query pencarian berdasarkan judul, penulis, atau penerbit
        $query = Buku::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('penulis', 'like', "%{$search}%")
                  ->orWhere('penerbit', 'like', "%{$search}%");
            });
        }

        // 2. Filter berdasarkan penerbit
        if ($request->filled('penerbit')) {
            $query->where('penerbit', $request->penerbit);
        }

        // Filter berdasarkan ketersediaan stok
        if ($request->filled('status')) {
            if ($request->status == 'tersedia') {
                $query->where('stok', '>', 0);
            } elseif ($request->status == 'habis') {
                $query->where('stok', '<=', 0);
            }
        }

        // 3. Pengurutan berdasarkan input sort
        switch ($request->sort) {
            case 'judul':
                $query->orderBy('judul', 'asc');
                break;

            case 'tahun':
                $query->orderBy('tahun_terbit', 'desc');
                break;

            case 'stok':
                $query->orderBy('stok', 'desc');
                break;

            default:
                $query->latest();
                break;
        }

        // 4. Paginasi 8 data dengan mempertahankan query URL
        $buku = $query->paginate(8)->withQueryString();

        // 5. Mengambil daftar penerbit unik
        $listPenerbit = Buku::select('penerbit')
            ->distinct()
            ->orderBy('penerbit', 'asc')
            ->pluck('penerbit');

        // 6. Mengirim data ke halaman home
        return view('home', compact('buku', 'listPenerbit'));
    }

    /**
     * Menampilkan halaman detail informasi sebuah buku untuk publik/mahasiswa.
     */
    public function show(Buku $buku)
    {
        // TODO 3: Mengembalikan view home_show dengan data buku
        return view('home_show', compact('buku'));
    }
}
