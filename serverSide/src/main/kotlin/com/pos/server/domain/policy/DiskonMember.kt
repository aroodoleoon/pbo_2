package com.pos.server.domain.policy

import com.pos.server.domain.model.Member
import com.pos.server.domain.model.Pelanggan

class DiskonMember : AturanDiskon {
    override fun memenuhiSyarat(subtotal: Long, pelanggan: Pelanggan?): Boolean {
        return pelanggan is Member && subtotal > 0
    }

    override fun hitungDiskon(subtotal: Long, pelanggan: Pelanggan?): Long {
        return if (pelanggan is Member) {
            pelanggan.hitungDiskon(subtotal)
        } else {
            0L
        }
    }

    override fun keterangan(): String = "Diskon Loyalty Member Toko"
}

