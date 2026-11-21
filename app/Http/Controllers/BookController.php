<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $books = Book::when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('pengarang', 'like', "%{$search}%")
                    ->orWhere('kode_buku', 'like', "%{$search}%")
                    ->orWhere('penerbit', 'like', "%{$search}%");
            });
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return inertia('Books/Index', [
            'books' => $books,
            'search' => $request->search ?? '',
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function cekKode(Request $request)
    {
        // Kode buku yang sedang diketik pengguna
        $kodeBuku = $request->query('kode_buku', '');

        // Periksa apakah kode buku sudah digunakan
        $sudahDipakai = Book::where('kode_buku', $kodeBuku)->exists();

        // Mengirim hasil pemeriksaan dalam format JSON
        return response()->json([
            'tersedia' => !$sudahDipakai,
        ]);
    }

    /**
     * Pencarian AJAX buku berdasarkan judul, pengarang,
     * kode buku, dan penerbit.
     */
    public function search(Request $request)
    {
        $kataKunci = $request->query('q', '');

        $books = Book::where(function ($query) use ($kataKunci) {
            $query->where('judul', 'like', "%{$kataKunci}%")
                ->orWhere('pengarang', 'like', "%{$kataKunci}%")
                ->orWhere('kode_buku', 'like', "%{$kataKunci}%")
                ->orWhere('penerbit', 'like', "%{$kataKunci}%");
        })
            ->latest()
            ->limit(10)
            ->get([
                'id',
                'kode_buku',
                'judul',
                'pengarang',
                'penerbit',
                'stok',
            ]);

        return response()->json($books);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Books/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_buku' => 'required|unique:books',
            'judul'     => 'required|max:255',
            'pengarang' => 'required|max:255',
            'penerbit'  => 'nullable|max:255',
            'tahun'     => 'nullable|digits:4',
            'stok'      => 'required|integer|min:0',
        ]);

        Book::create($validated);

        return redirect()->route('books.index')
            ->with('success', 'Buku berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        return inertia('Books/Edit', [
            'book' => $book,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'kode_buku' => 'required|unique:books,kode_buku,' . $book->id,
            'judul'     => 'required|max:255',
            'pengarang' => 'required|max:255',
            'penerbit'  => 'nullable|max:255',
            'tahun'     => 'nullable|digits:4',
            'stok'      => 'required|integer|min:0',
        ]);

        $book->update($validated);

        return redirect()->route('books.index')
            ->with('success', 'Buku berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'Buku berhasil dihapus');
    }
}
