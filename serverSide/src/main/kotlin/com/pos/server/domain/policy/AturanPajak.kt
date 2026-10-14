package com.pos.server.domain.policy

class AturanPajak(
    val tarifPajak: Double = 0.11 // PPN 11% (default)
) {
    fun hitungPajak(dasarPengenaanPajak: Long): Long {
        if (dasarPengenaanPajak <= 0) return 0L
        return (dasarPengenaanPajak * tarifPajak).toLong()
    }
}

