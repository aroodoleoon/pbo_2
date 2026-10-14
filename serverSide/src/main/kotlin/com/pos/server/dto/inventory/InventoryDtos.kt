package com.pos.server.dto.inventory

import kotlinx.serialization.Serializable

@Serializable
data class StockReceiptRequest(
    val productId: String,
    val quantity: Int,
    val source: String,
    val notes: String? = null
)

@Serializable
data class StockAdjustmentRequest(
    val productId: String,
    val quantity: Int,
    val type: String, // OUT_DAMAGED, OUT_LOST, OUT_OTHER
    val reason: String
)

@Serializable
data class StockMovementDto(
    val id: String,
    val productId: String,
    val type: String,
    val quantity: Int,
    val reason: String,
    val employeeId: String,
    val reference: String?,
    val timestamp: String
)

