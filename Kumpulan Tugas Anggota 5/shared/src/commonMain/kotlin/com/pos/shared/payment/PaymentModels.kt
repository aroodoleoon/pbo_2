package com.pos.shared.payment

import kotlinx.serialization.Serializable

@Serializable
data class SaleItemRequest(val itemId: String, val quantity: Int)

@Serializable
data class SaleRequest(
    val transactionId: String,
    val cashierId: String,
    val customerId: String? = null,
    val items: List<SaleItemRequest>,
    val discountAmount: Long = 0
)

@Serializable
data class CashPaymentRequest(val sale: SaleRequest, val amountPaid: Long)

@Serializable
data class PaymentResult(
    val transactionId: String,
    val paymentId: String,
    val subtotal: Long,
    val discountAmount: Long,
    val total: Long,
    val amountPaid: Long,
    val changeAmount: Long,
    val status: String
)

@Serializable
data class TransactionHistoryItem(
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

@Serializable
data class SalesReportRow(
    val salesDate: String,
    val transactionCount: Long,
    val grossSales: Long,
    val discountTotal: Long,
    val netSales: Long
)

@Serializable
data class ApiError(val message: String)
