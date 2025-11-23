<div>
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Barang Keluar / Penyesuaian Stok</h3>
                <p class="text-subtitle text-muted">Lakukan penyesuaian stok untuk barang rusak, pemakaian internal, dll.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Penyesuaian Stok</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Form Penyesuaian Stok</h4>
            </div>
            <div class="card-body">
                @if (session()->has('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form wire:submit.prevent="save">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_name">Cari Produk</label>
                                <input type="text" class="form-control @error('product_id') is-invalid @enderror" wire:model.debounce.300ms="search" placeholder="Ketik nama atau kode produk">
                                @if(!empty($search))
                                    <div class="list-group">
                                        @if(count($products) > 0)
                                            @foreach($products as $product)
                                                <a href="#" wire:click.prevent="selectProduct({{ $product->id }}, '{{ $product->name }}')" class="list-group-item list-group-item-action">
                                                    {{ $product->name }} - (Stok: {{ $product->stock }})
                                                </a>
                                            @endforeach
                                        @else
                                            <span class="list-group-item">Produk tidak ditemukan</span>
                                        @endif
                                    </div>
                                @endif
                                @error('product_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                             <div class="form-group">
                                <label for="product_name_selected">Produk Terpilih</label>
                                <input type="text" id="product_name_selected" class="form-control" wire:model="product_name" readonly disabled>
                            </div>

                            <div class="form-group">
                                <label for="quantity">Jumlah</label>
                                <input type="number" id="quantity" class="form-control @error('quantity') is-invalid @enderror" wire:model="quantity" placeholder="Jumlah barang keluar">
                                @error('quantity') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type">Tipe Penyesuaian</label>
                                <select id="type" class="form-control @error('type') is-invalid @enderror" wire:model="type">
                                    <option value="">Pilih Tipe</option>
                                    @foreach($types as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="notes">Catatan</label>
                                <textarea id="notes" class="form-control" wire:model="notes" rows="4" placeholder="Catatan tambahan (opsional)"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" wire:click="resetForm" class="btn btn-secondary">Batal</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Riwayat Penyesuaian Stok</h4>
            </div>
            <div class="card-body">
                 <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Produk</th>
                                <th>Jumlah</th>
                                <th>Tipe</th>
                                <th>User</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($adjustments as $index => $adjustment)
                                <tr>
                                    <td>{{ $adjustments->firstItem() + $index }}</td>
                                    <td>{{ $adjustment->created_at->format('d-m-Y H:i') }}</td>
                                    <td>{{ $adjustment->product->name }}</td>
                                    <td>{{ $adjustment->quantity }}</td>
                                    <td>{{ $types[$adjustment->type] ?? $adjustment->type }}</td>
                                    <td>{{ $adjustment->user->name }}</td>
                                    <td>{{ $adjustment->notes }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada riwayat penyesuaian stok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $adjustments->links() }}
            </div>
        </div>
    </section>
</div>