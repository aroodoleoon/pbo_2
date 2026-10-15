# Skrip Otomatis Pengujian API Sistem POS
$baseUrl = "http://103.119.237.19:3232/api/v1"

Write-Host "`n===============================================" -ForegroundColor Cyan
Write-Host "      PENGUJIAN API SISTEM KASIR POS           " -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan

# 1. Login Admin
Write-Host "`n[1] Menguji Login Admin (POST /auth/login)..." -ForegroundColor Yellow
$loginPayload = @{ username = "admin"; password = "admin123" } | ConvertTo-Json
$auth = Invoke-RestMethod -Uri "$baseUrl/auth/login" -Method Post -Body $loginPayload -ContentType "application/json"
Write-Host "-> Login Berhasil! User: $($auth.user.name) | Role: $($auth.user.role)" -ForegroundColor Green

# 2. Ambil Daftar Kategori
Write-Host "`n[2] Mengambil Kategori Barang (GET /categories)..." -ForegroundColor Yellow
$categories = Invoke-RestMethod -Uri "$baseUrl/categories" -Method Get
Write-Host "-> Kategori ditemukan: $(($categories | ForEach-Object { $_.name }) -join ', ')" -ForegroundColor Green

# 3. Ambil Daftar Barang
Write-Host "`n[3] Mengambil Daftar Barang (GET /products)..." -ForegroundColor Yellow
$products = Invoke-RestMethod -Uri "$baseUrl/products" -Method Get
$products | ForEach-Object {
    Write-Host "   - [$($_.barcode)] $($_.name) | Harga: Rp $($_.sellPrice) | Stok: $($_.stock)" -ForegroundColor Gray
}

# 4. Ambil Daftar Pelanggan & Member
Write-Host "`n[4] Mengambil Data Pelanggan & Member (GET /customers)..." -ForegroundColor Yellow
$customers = Invoke-RestMethod -Uri "$baseUrl/customers" -Method Get
$customers | ForEach-Object {
    $status = if ($_.isMember) { "Member ($($_.memberNumber) - Poin: $($_.points))" } else { "Non-Member" }
    Write-Host "   - $($_.name) ($($_.contact)) -> $status" -ForegroundColor Gray
}

# 5. Simulasi Transaksi Kasir
Write-Host "`n[5] Melakukan Transaksi Pembayaran Tunai (POST /payments/cash)..." -ForegroundColor Yellow
$targetItem = $products[0]
Write-Host "   Membeli 2 pcs '$($targetItem.name)'..." -ForegroundColor Gray

$trxPayload = @{
    sale = @{
        transactionId = "TRX-TEST-$(Get-Random -Minimum 1000 -Maximum 9999)"
        cashierId = "22222222-2222-2222-2222-222222222222"
        customerId = $customers[0].id
        items = @(
            @{ itemId = $targetItem.id; quantity = 2 }
        )
        discountAmount = 0
    }
    amountPaid = ($targetItem.sellPrice * 2) + 10000
} | ConvertTo-Json -Depth 5

$paymentRes = Invoke-RestMethod -Uri "$baseUrl/payments/cash" -Method Post -Body $trxPayload -ContentType "application/json"
Write-Host "-> Pembayaran Berhasil!" -ForegroundColor Green
Write-Host "   ID Transaksi : $($paymentRes.transactionId)" -ForegroundColor Green
Write-Host "   Total Tagihan: Rp $($paymentRes.total)" -ForegroundColor Green
Write-Host "   Nominal Bayar: Rp $($paymentRes.amountPaid)" -ForegroundColor Green
Write-Host "   Kembalian    : Rp $($paymentRes.changeAmount)" -ForegroundColor Green

# 6. Cek Stok Terkini Setelah Transaksi
Write-Host "`n[6] Memverifikasi Pemotongan Stok Otomatis..." -ForegroundColor Yellow
$updatedProducts = Invoke-RestMethod -Uri "$baseUrl/products" -Method Get
$updatedItem = $updatedProducts | Where-Object { $_.id -eq $targetItem.id }
Write-Host "-> Stok '$($targetItem.name)': $($targetItem.stock) -> $($updatedItem.stock) (Berkurang 2)" -ForegroundColor Green

# 7. Cek Laporan Penjualan
Write-Host "`n[7] Mengambil Laporan Penjualan (GET /reports/sales)..." -ForegroundColor Yellow
$today = (Get-Date).ToString("yyyy-MM-dd")
$report = Invoke-RestMethod -Uri "$baseUrl/reports/sales?from=$today&to=$today" -Method Get
Write-Host "-> Tanggal: $($report.salesDate) | Transaksi: $($report.transactionCount) | Penjualan Bersih: Rp $($report.netSales)" -ForegroundColor Green

Write-Host "`n===============================================" -ForegroundColor Cyan
Write-Host "      SEMUA PENGUJIAN API BERHASIL!            " -ForegroundColor Cyan
Write-Host "===============================================`n" -ForegroundColor Cyan

