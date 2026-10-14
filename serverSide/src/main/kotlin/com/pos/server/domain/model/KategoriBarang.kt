package com.pos.server.domain.model

import com.pos.server.domain.exception.ValidasiException
import java.util.UUID

data class KategoriBarang(
    val id: String = UUID.randomUUID().toString(),
    var nama: String
) {
    init {
        val trimmed = nama.trim()
        if (trimmed.isEmpty()) {
            throw ValidasiException("Nama kategori barang tidak boleh kosong.")
        }
        nama = trimmed
    }
}

