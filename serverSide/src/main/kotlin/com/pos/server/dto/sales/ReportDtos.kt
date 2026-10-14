package com.pos.server.dto.sales

import kotlinx.serialization.Serializable

@Serializable
data class SalesReportRowDto(
    val salesDate: String,
    val transactionCount: Long,
    val grossSales: Long,
    val discountTotal: Long,
    val netSales: Long
)

@Serializable
data class TransactionHistoryItemDto(
    val transactionId: String,
    val transactionDate: String,
    val cashierId: String,
    val customerId: String?,
    val subtotal: Long,
    val discountAmount: Long,
    val total: Long,
    val paymentMethod: String,
    val amountPaid: Long,
    val changeAmount: Long,
    val paymentStatus: String
)

