package com.pos.server.domain.policy

import com.pos.server.domain.model.Pelanggan

class DiskonMinimalBelanja(
    val minimalBelanja: Long = 100_000L,
    val persentaseDiskon: Double = 0.05
) : AturanDiskon {
    override fun memenuhiSyarat(subtotal: Long, pelanggan: Pelanggan?): Boolean {
        return subtotal >= minimalBelanja
    }

    override fun hitungDiskon(subtotal: Long, pelanggan: Pelanggan?): Long {
        return if (memenuhiSyarat(subtotal, pelanggan)) {
            (subtotal * persentaseDiskon).toLong()
        } else {
            0L
        }
    }

    override fun keterangan(): String =
        "Diskon Pembelian di atas Rp $minimalBelanja (${(persentaseDiskon * 100).toInt()}%)"
}

