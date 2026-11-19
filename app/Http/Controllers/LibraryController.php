<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();
        $activeLoans = DB::table('dbo.Loans')
            ->select('BookID', DB::raw('COUNT(*) AS LoanedCopies'))
            ->whereNull('ReturnedAt')
            ->groupBy('BookID');

        $books = DB::table('dbo.Books as b')
            ->leftJoinSub($activeLoans, 'active_loans', function ($join): void {
                $join->on('active_loans.BookID', '=', 'b.BookID');
            })
            ->select(
                'b.BookID',
                'b.ISBN',
                'b.Title',
                'b.Author',
                'b.PublishedYear',
                'b.TotalCopies',
                DB::raw('COALESCE(active_loans.LoanedCopies, 0) AS LoanedCopies')
            )
            ->orderBy('b.Title')
            ->get();

        $members = DB::table('dbo.Members')
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        $loans = DB::table('dbo.Loans as l')
            ->join('dbo.Books as b', 'b.BookID', '=', 'l.BookID')
            ->join('dbo.Members as m', 'm.MemberID', '=', 'l.MemberID')
            ->select(
                'l.LoanID',
                'l.LoanDate',
                'l.DueDate',
                'b.Title AS BookTitle',
                'm.FirstName',
                'm.LastName'
            )
            ->whereNull('l.ReturnedAt')
            ->orderBy('l.DueDate')
            ->get();

        $fines = DB::table('dbo.Fines as f')
            ->join('dbo.Loans as l', 'l.LoanID', '=', 'f.LoanID')
            ->join('dbo.Books as b', 'b.BookID', '=', 'l.BookID')
            ->join('dbo.Members as m', 'm.MemberID', '=', 'l.MemberID')
            ->select(
                'f.FineID',
                'f.Amount',
                'f.IssuedAt',
                'f.PaidAt',
                'l.LoanID',
                'b.Title AS BookTitle',
                'm.FirstName',
                'm.LastName'
            )
            ->orderBy('f.PaidAt')
            ->orderByDesc('f.IssuedAt')
            ->get();

        $fineCandidates = DB::table('dbo.Loans as l')
            ->join('dbo.Books as b', 'b.BookID', '=', 'l.BookID')
            ->join('dbo.Members as m', 'm.MemberID', '=', 'l.MemberID')
            ->select('l.LoanID', 'b.Title AS BookTitle', 'm.FirstName', 'm.LastName', 'l.DueDate')
            ->whereNull('l.ReturnedAt')
            ->where('l.DueDate', '<', $today)
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('dbo.Fines as f')
                    ->whereColumn('f.LoanID', 'l.LoanID');
            })
            ->orderBy('l.DueDate')
            ->get();

        return view('dashboard', [
            'books' => $books,
            'members' => $members,
            'loans' => $loans,
            'fines' => $fines,
            'fineCandidates' => $fineCandidates,
            'today' => $today,
            'overdueCount' => DB::table('dbo.Loans')
                ->whereNull('ReturnedAt')
                ->where('DueDate', '<', $today)
                ->count(),
            'outstandingFines' => DB::table('dbo.Fines')
                ->whereNull('PaidAt')
                ->sum('Amount'),
        ]);
    }

    public function storeBook(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ISBN' => ['required', 'string', 'max:20', 'unique:sqlsrv.dbo.Books,ISBN'],
            'Title' => ['required', 'string', 'max:200'],
            'Author' => ['required', 'string', 'max:150'],
            'PublishedYear' => ['nullable', 'integer', 'between:1,9999'],
            'TotalCopies' => ['required', 'integer', 'min:1'],
        ]);

        DB::table('dbo.Books')->insert($data);

        return back()->with('success', 'Buku berhasil ditambahkan.');
    }

    public function storeMember(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'FirstName' => ['required', 'string', 'max:80'],
            'LastName' => ['required', 'string', 'max:80'],
            'Email' => ['required', 'email', 'max:254', 'unique:sqlsrv.dbo.Members,Email'],
        ]);

        DB::table('dbo.Members')->insert($data);

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function storeLoan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'BookID' => ['required', 'integer', 'exists:sqlsrv.dbo.Books,BookID'],
            'MemberID' => ['required', 'integer', 'exists:sqlsrv.dbo.Members,MemberID'],
            'DueDate' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $result = DB::transaction(function () use ($data): string {
            $book = DB::table('dbo.Books')
                ->where('BookID', $data['BookID'])
                ->lockForUpdate()
                ->first();

            if ($book === null) {
                return 'unavailable';
            }

            $activeLoans = DB::table('dbo.Loans')
                ->where('BookID', $book->BookID)
                ->whereNull('ReturnedAt')
                ->count();

            if ($activeLoans >= $book->TotalCopies) {
                return 'unavailable';
            }

            $memberIsActive = DB::table('dbo.Members')
                ->where('MemberID', $data['MemberID'])
                ->where('IsActive', 1)
                ->exists();

            if (! $memberIsActive) {
                return 'inactive';
            }

            DB::table('dbo.Loans')->insert([
                'BookID' => $book->BookID,
                'MemberID' => $data['MemberID'],
                'LoanDate' => now()->toDateString(),
                'DueDate' => $data['DueDate'],
            ]);

            return 'created';
        });

        if ($result === 'unavailable') {
            return back()->withInput()->withErrors([
                'BookID' => 'Stok buku tidak tersedia untuk dipinjam.',
            ]);
        }

        if ($result === 'inactive') {
            return back()->withInput()->withErrors([
                'MemberID' => 'Anggota tidak aktif dan tidak dapat meminjam buku.',
            ]);
        }

        return back()->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function returnLoan(int $loan): RedirectResponse
    {
        $updated = DB::table('dbo.Loans')
            ->where('LoanID', $loan)
            ->whereNull('ReturnedAt')
            ->update(['ReturnedAt' => now()->toDateString()]);

        if ($updated === 0) {
            return back()->withErrors([
                'loan' => 'Peminjaman tidak ditemukan atau buku sudah dikembalikan.',
            ]);
        }

        return back()->with('success', 'Buku berhasil ditandai sudah dikembalikan.');
    }

    public function storeFine(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'LoanID' => ['required', 'integer', 'exists:sqlsrv.dbo.Loans,LoanID'],
            'Amount' => ['required', 'numeric', 'gt:0', 'max:999999.99'],
        ]);

        $result = DB::transaction(function () use ($data): string {
            $loan = DB::table('dbo.Loans')
                ->where('LoanID', $data['LoanID'])
                ->lockForUpdate()
                ->first();

            if (
                $loan === null
                || $loan->ReturnedAt !== null
                || $loan->DueDate >= now()->toDateString()
            ) {
                return 'not_overdue';
            }

            $alreadyFined = DB::table('dbo.Fines')
                ->where('LoanID', $loan->LoanID)
                ->exists();

            if ($alreadyFined) {
                return 'already_fined';
            }

            DB::table('dbo.Fines')->insert([
                'LoanID' => $loan->LoanID,
                'Amount' => $data['Amount'],
                'IssuedAt' => now()->toDateString(),
            ]);

            return 'created';
        });

        if ($result === 'not_overdue') {
            return back()->withInput()->withErrors([
                'LoanID' => 'Denda hanya dapat dicatat untuk peminjaman aktif yang terlambat.',
            ]);
        }

        if ($result === 'already_fined') {
            return back()->withInput()->withErrors([
                'LoanID' => 'Peminjaman ini sudah memiliki denda.',
            ]);
        }

        return back()->with('success', 'Denda berhasil dicatat.');
    }

    public function payFine(int $fine): RedirectResponse
    {
        $updated = DB::table('dbo.Fines')
            ->where('FineID', $fine)
            ->whereNull('PaidAt')
            ->update(['PaidAt' => now()->toDateString()]);

        if ($updated === 0) {
            return back()->withErrors([
                'fine' => 'Denda tidak ditemukan atau sudah dibayar.',
            ]);
        }

        return back()->with('success', 'Pembayaran denda berhasil dicatat.');
    }
}
