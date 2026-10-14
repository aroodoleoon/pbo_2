package com.pos.server.domain.enums

enum class TransactionStatus {
    PENDING,
    COMPLETED,
    CANCELLED;

    companion object {
        fun fromString(value: String): TransactionStatus = when (value.uppercase()) {
            "PENDING", "DRAFT" -> PENDING
            "COMPLETED", "BERHASIL", "LUNAS", "SUCCESS" -> COMPLETED
            "CANCELLED", "DIBATALKAN" -> CANCELLED
            else -> throw IllegalArgumentException("Status transaksi '$value' tidak valid.")
        }
    }
}

