package com.pos.server.domain.enums

enum class StockMovementType(val menambah: Boolean) {
    IN(true),                     // Barang masuk / penerimaan
    OUT_SALE(false),              // Keluar karena penjualan
    OUT_DAMAGED(false),           // Keluar karena barang rusak
    OUT_LOST(false),              // Keluar karena barang hilang
    OUT_OTHER(false),             // Keluar karena alasan lain
    CANCEL_SALE(true);            // Stok kembali karena transaksi dibatalkan

    companion object {
        fun fromString(value: String): StockMovementType = when (value.uppercase()) {
            "IN", "MASUK" -> IN
            "OUT_SALE", "KELUAR_JUAL" -> OUT_SALE
            "OUT_DAMAGED", "KELUAR_RUSAK", "RUSAK" -> OUT_DAMAGED
            "OUT_LOST", "KELUAR_HILANG", "HILANG" -> OUT_LOST
            "OUT_OTHER", "KELUAR_LAIN", "LAIN" -> OUT_OTHER
            "CANCEL_SALE", "BATAL_JUAL" -> CANCEL_SALE
            else -> throw IllegalArgumentException("Jenis pergerakan stok '$value' tidak valid.")
        }
    }
}

