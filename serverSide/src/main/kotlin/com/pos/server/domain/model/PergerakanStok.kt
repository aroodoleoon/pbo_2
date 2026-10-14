package com.pos.server.domain.model

import com.pos.server.domain.enums.StockMovementType
import java.time.Instant
import java.util.UUID

/**
 * Entitas PergerakanStok mencatat setiap perubahan kuantitas stok barang (Modul 2 Alex).
 */
data class PergerakanStok(
    val id: String = UUID.randomUUID().toString(),
    val barangId: String,
    val jenis: StockMovementType,
    val jumlah: Int,
    val alasan: String,
    val karyawanId: String,
    val referensi: String? = null,
    val dibuatPada: Instant = Instant.now()
)

