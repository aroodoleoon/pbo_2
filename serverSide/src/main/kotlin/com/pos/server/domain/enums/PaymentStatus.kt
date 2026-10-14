package com.pos.server.domain.enums

enum class PaymentStatus {
    PENDING,
    SUCCESS,
    FAILED;

    companion object {
        fun fromString(value: String): PaymentStatus = when (value.uppercase()) {
            "PENDING" -> PENDING
            "SUCCESS", "BERHASIL" -> SUCCESS
            "FAILED", "GAGAL" -> FAILED
            else -> throw IllegalArgumentException("Status pembayaran '$value' tidak valid.")
        }
    }
}

