<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman katalog buku dengan fitur pencarian, filter, sorting, dan paginasi.
     */
    public function index(Request $request)
    {
        $query = Buku::query();

        // 1. Pencarian: judul, penulis, atau penerbit
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                ->orWhere('penulis', 'like', "%{$search}%")
                ->orWhere('penerbit', 'like', "%{$search}%");
            });
        }

        // 2. Filter penerbit
        if ($request->filled('penerbit')) {
            $query->where('penerbit', $request->penerbit);
        }

        // 2. Filter ketersediaan stok
        if ($request->status === 'tersedia') {
            $query->where('stok', '>', 0);
        } elseif ($request->status === 'habis') {
            $query->where('stok', '<=', 0);
        }

        // 3. Pengurutan
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
        }

        // 4. Paginasi 8 data, kata kunci tetap terbawa
        $buku = $query->paginate(8)->withQueryString();

        // 5. List penerbit unik untuk dropdown
        $listPenerbit = Buku::select('penerbit')->distinct()->orderBy('penerbit')->pluck('penerbit');

        // 6. Return view
        return view('home', compact('buku', 'listPenerbit'));
    }

    /**
     * Menampilkan halaman detail informasi sebuah buku untuk publik/mahasiswa.
     */
    public function show(Buku $buku)
    {
        // TODO 3: Kembalikan view 'home_show' membawa data $buku
        return view('home_show', compact('buku'));
    }
}
