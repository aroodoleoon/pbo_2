package com.pos.server.domain.enums

enum class PaymentMethod {
    CASH,
    QRIS,
    TRANSFER,
    DEBIT;

    companion object {
        fun fromString(value: String): PaymentMethod = when (value.uppercase()) {
            "CASH", "TUNAI" -> CASH
            "QRIS" -> QRIS
            "TRANSFER" -> TRANSFER
            "DEBIT" -> DEBIT
            else -> throw IllegalArgumentException("Metode pembayaran '$value' tidak didukung.")
        }
    }
}

