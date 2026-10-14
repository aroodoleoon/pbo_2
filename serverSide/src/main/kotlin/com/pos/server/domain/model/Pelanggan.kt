package com.pos.server.domain.model

import com.pos.server.domain.exception.ValidasiException
import java.util.UUID

enum class TingkatMember(val persentaseDiskon: Double) {
    REGULER(0.05),  // Diskon 5%
    SILVER(0.08),   // Diskon 8%
    GOLD(0.12)      // Diskon 12%
}

/**
 * Model domain Pelanggan dan Member mengadopsi rancangan Modul 4 (Dhia).
 */
open class Pelanggan(
    val id: String = UUID.randomUUID().toString(),
    var nama: String,
    var kontak: String? = null
) {
    open val isMember: Boolean = false

    init {
        if (nama.trim().isEmpty()) throw ValidasiException("Nama pelanggan tidak boleh kosong.")
    }

    open fun hitungDiskon(totalBelanja: Long): Long = 0L
}

class Member(
    id: String = UUID.randomUUID().toString(),
    nama: String,
    kontak: String? = null,
    val nomorMember: String,
    var poin: Int = 0,
    var tingkatMember: TingkatMember = TingkatMember.REGULER
) : Pelanggan(id, nama, kontak) {

    override val isMember: Boolean = true

    override fun hitungDiskon(totalBelanja: Long): Long {
        return (totalBelanja * tingkatMember.persentaseDiskon).toLong()
    }

    fun tambahPoin(totalBelanja: Long): Int {
        val poinDidapat = (totalBelanja / 10000L).toInt()
        poin += poinDidapat
        perbaruiTingkatMember()
        return poinDidapat
    }

    fun gunakanPoin(jumlahTukar: Int): Long {
        if (jumlahTukar <= 0) return 0L
        return if (jumlahTukar <= poin) {
            poin -= jumlahTukar
            jumlahTukar * 100L // 1 poin bernilai Rp 100
        } else {
            0L
        }
    }

    private fun perbaruiTingkatMember() {
        tingkatMember = when {
            poin >= 200 -> TingkatMember.GOLD
            poin >= 100 -> TingkatMember.SILVER
            else -> TingkatMember.REGULER
        }
    }
}

