@extends('layouts.app')

@section('title', 'LibrarySystem — Dashboard')

@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">MANAJEMEN PERPUSTAKAAN</p>
            <h1>Dashboard</h1>
            <p class="muted">Kelola koleksi, anggota, peminjaman, dan denda.</p>
        </div>
        <div class="today">{{ \Illuminate\Support\Carbon::parse($today)->translatedFormat('l, d F Y') }}</div>
    </section>

    <section class="stats" aria-label="Ringkasan">
        <article class="stat-card">
            <span class="stat-label">Judul buku</span>
            <strong>{{ $books->count() }}</strong>
        </article>
        <article class="stat-card">
            <span class="stat-label">Anggota</span>
            <strong>{{ $members->count() }}</strong>
        </article>
        <article class="stat-card">
            <span class="stat-label">Terlambat</span>
            <strong class="{{ $overdueCount > 0 ? 'text-danger' : '' }}">{{ $overdueCount }}</strong>
        </article>
        <article class="stat-card">
            <span class="stat-label">Denda belum dibayar</span>
            <strong>Rp {{ number_format((float) $outstandingFines, 2, ',', '.') }}</strong>
        </article>
    </section>

    <section class="panel" id="books">
        <div class="section-heading">
            <div>
                <p class="eyebrow">KOLEKSI</p>
                <h2>Daftar buku</h2>
            </div>
            <span class="count">{{ $books->count() }} judul</span>
        </div>
        <form class="form-grid" action="{{ route('books.store') }}" method="POST">
            @csrf
            <label>ISBN
                <input name="ISBN" maxlength="20" value="{{ old('ISBN') }}" required>
                @error('ISBN') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>Judul
                <input name="Title" maxlength="200" value="{{ old('Title') }}" required>
                @error('Title') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>Penulis
                <input name="Author" maxlength="150" value="{{ old('Author') }}" required>
                @error('Author') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>Tahun terbit
                <input name="PublishedYear" type="number" min="1" max="9999" value="{{ old('PublishedYear') }}">
                @error('PublishedYear') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>Jumlah eksemplar
                <input name="TotalCopies" type="number" min="1" value="{{ old('TotalCopies', 1) }}" required>
                @error('TotalCopies') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <button type="submit">Tambah buku</button>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>ISBN</th><th>Judul</th><th>Penulis</th><th>Tahun</th><th>Tersedia</th></tr>
                </thead>
                <tbody>
                    @forelse ($books as $book)
                        <tr>
                            <td>{{ $book->ISBN }}</td>
                            <td class="primary-cell">{{ $book->Title }}</td>
                            <td>{{ $book->Author }}</td>
                            <td>{{ $book->PublishedYear ?? '—' }}</td>
                            <td>{{ max(0, $book->TotalCopies - $book->LoanedCopies) }} / {{ $book->TotalCopies }}</td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="5">Belum ada buku.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel" id="members">
        <div class="section-heading">
            <div>
                <p class="eyebrow">PELANGGAN</p>
                <h2>Anggota</h2>
            </div>
            <span class="count">{{ $members->count() }} anggota</span>
        </div>
        <form class="form-grid member-form" action="{{ route('members.store') }}" method="POST">
            @csrf
            <label>Nama depan
                <input name="FirstName" maxlength="80" value="{{ old('FirstName') }}" required>
                @error('FirstName') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>Nama belakang
                <input name="LastName" maxlength="80" value="{{ old('LastName') }}" required>
                @error('LastName') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>Email
                <input name="Email" type="email" maxlength="254" value="{{ old('Email') }}" required>
                @error('Email') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <button type="submit">Tambah anggota</button>
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama</th><th>Email</th><th>Bergabung</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td class="primary-cell">{{ $member->FirstName }} {{ $member->LastName }}</td>
                            <td>{{ $member->Email }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($member->JoinedAt)->format('d M Y') }}</td>
                            <td><span class="badge {{ $member->IsActive ? 'badge-success' : 'badge-muted' }}">{{ $member->IsActive ? 'Aktif' : 'Nonaktif' }}</span></td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="4">Belum ada anggota.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel" id="loans">
        <div class="section-heading">
            <div>
                <p class="eyebrow">SIRKULASI</p>
                <h2>Peminjaman aktif</h2>
            </div>
            <span class="count">{{ $loans->count() }} peminjaman</span>
        </div>
        <form class="form-grid loan-form" action="{{ route('loans.store') }}" method="POST">
            @csrf
            <label>Buku
                <select name="BookID" required>
                    <option value="">Pilih buku tersedia</option>
                    @foreach ($books as $book)
                        @php($available = $book->TotalCopies - $book->LoanedCopies)
                        @if ($available > 0)
                            <option value="{{ $book->BookID }}" @selected(old('BookID') == $book->BookID)>
                                {{ $book->Title }} (tersedia {{ $available }})
                            </option>
                        @endif
                    @endforeach
                </select>
                @error('BookID') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>Anggota
                <select name="MemberID" required>
                    <option value="">Pilih anggota aktif</option>
                    @foreach ($members->where('IsActive', true) as $member)
                        <option value="{{ $member->MemberID }}" @selected(old('MemberID') == $member->MemberID)>
                            {{ $member->FirstName }} {{ $member->LastName }}
                        </option>
                    @endforeach
                </select>
                @error('MemberID') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>Tanggal jatuh tempo
                <input name="DueDate" type="date" min="{{ $today }}" value="{{ old('DueDate') }}" required>
                @error('DueDate') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <button type="submit">Catat peminjaman</button>
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Buku</th><th>Anggota</th><th>Tanggal pinjam</th><th>Jatuh tempo</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($loans as $loan)
                        <tr>
                            <td class="primary-cell">{{ $loan->BookTitle }}</td>
                            <td>{{ $loan->FirstName }} {{ $loan->LastName }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($loan->LoanDate)->format('d M Y') }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($loan->DueDate)->format('d M Y') }}</td>
                            <td>
                                @if ($loan->DueDate < $today)
                                    <span class="badge badge-danger">Terlambat</span>
                                @else
                                    <span class="badge badge-success">Dipinjam</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('loans.return', $loan->LoanID) }}" method="POST">
                                    @csrf
                                    <button class="button-small button-secondary" type="submit">Kembalikan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="6">Tidak ada peminjaman aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel" id="fines">
        <div class="section-heading">
            <div>
                <p class="eyebrow">PEMBAYARAN</p>
                <h2>Denda</h2>
            </div>
            <span class="count">{{ $fines->count() }} denda</span>
        </div>
        <form class="form-grid fine-form" action="{{ route('fines.store') }}" method="POST">
            @csrf
            <label>Peminjaman terlambat
                <select name="LoanID" required>
                    <option value="">Pilih peminjaman</option>
                    @foreach ($fineCandidates as $candidate)
                        <option value="{{ $candidate->LoanID }}" @selected(old('LoanID') == $candidate->LoanID)>
                            {{ $candidate->BookTitle }} — {{ $candidate->FirstName }} {{ $candidate->LastName }}
                            (jatuh tempo {{ \Illuminate\Support\Carbon::parse($candidate->DueDate)->format('d M Y') }})
                        </option>
                    @endforeach
                </select>
                @error('LoanID') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>Jumlah denda (Rp)
                <input name="Amount" type="number" min="0.01" max="999999.99" step="0.01" value="{{ old('Amount') }}" required>
                @error('Amount') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <button type="submit" @disabled($fineCandidates->isEmpty())>Catat denda</button>
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Buku</th><th>Anggota</th><th>Diterbitkan</th><th>Jumlah</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($fines as $fine)
                        <tr>
                            <td class="primary-cell">{{ $fine->BookTitle }}</td>
                            <td>{{ $fine->FirstName }} {{ $fine->LastName }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($fine->IssuedAt)->format('d M Y') }}</td>
                            <td>Rp {{ number_format((float) $fine->Amount, 2, ',', '.') }}</td>
                            <td>
                                @if ($fine->PaidAt)
                                    <span class="badge badge-success">Lunas</span>
                                @else
                                    <span class="badge badge-danger">Belum dibayar</span>
                                @endif
                            </td>
                            <td>
                                @if (! $fine->PaidAt)
                                    <form action="{{ route('fines.pay', $fine->FineID) }}" method="POST">
                                        @csrf
                                        <button class="button-small button-secondary" type="submit">Tandai lunas</button>
                                    </form>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="6">Belum ada denda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
