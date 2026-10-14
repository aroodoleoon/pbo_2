package com.pos.server.domain.policy

import com.pos.server.domain.model.Pelanggan

interface AturanDiskon {
    fun memenuhiSyarat(subtotal: Long, pelanggan: Pelanggan?): Boolean
    fun hitungDiskon(subtotal: Long, pelanggan: Pelanggan?): Long
    fun keterangan(): String
}

