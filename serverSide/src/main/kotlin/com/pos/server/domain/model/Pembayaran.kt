package com.pos.server.domain.model

import com.pos.server.domain.enums.PaymentMethod
import com.pos.server.domain.enums.PaymentStatus
import com.pos.server.domain.exception.ValidasiException
import java.time.Instant
import java.util.UUID

/**
 * Entitas Pembayaran POS mengadopsi rancangan Modul 5 (Josua).
 * Mengatur aturan bisnis pembayaran CASH vs NON-TUNAI (QRIS, TRANSFER, DEBIT).
 */
class Pembayaran(
    val id: String = UUID.randomUUID().toString(),
    val transaksiId: String,
    val metode: PaymentMethod,
    val nominalBayar: Long,
    val totalTagihan: Long,
    var status: PaymentStatus = PaymentStatus.PENDING,
    val dibuatPada: Instant = Instant.now()
) {
    val kembalian: Long

    init {
        if (nominalBayar < 0) throw ValidasiException("Nominal bayar tidak boleh negatif.")
        if (totalTagihan < 0) throw ValidasiException("Total tagihan tidak valid.")

        when (metode) {
            PaymentMethod.CASH -> {
                if (nominalBayar < totalTagihan) {
                    throw ValidasiException("Pembayaran tunai kurang dari total belanja (kurang: ${totalTagihan - nominalBayar}).")
                }
                kembalian = nominalBayar - totalTagihan
            }
            PaymentMethod.QRIS, PaymentMethod.TRANSFER, PaymentMethod.DEBIT -> {
                // Untuk non-tunai, nominal bayar = total akhir dan kembalian = 0
                kembalian = 0L
            }
        }
        status = PaymentStatus.SUCCESS
    }
}

