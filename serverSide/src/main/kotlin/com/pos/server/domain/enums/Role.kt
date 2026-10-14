package com.pos.server.domain.enums

enum class Role {
    ADMIN,
    CASHIER;

    companion object {
        fun fromString(value: String): Role = when (value.uppercase()) {
            "ADMIN" -> ADMIN
            "CASHIER", "KASIR" -> CASHIER
            else -> throw IllegalArgumentException("Role '$value' tidak dikenal.")
        }
    }
}

