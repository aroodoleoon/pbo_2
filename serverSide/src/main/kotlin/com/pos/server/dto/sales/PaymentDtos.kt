package com.pos.server.dto.sales

import kotlinx.serialization.Serializable

@Serializable
data class ProcessPaymentRequest(
    val method: String, // CASH, QRIS, TRANSFER, DEBIT
    val amountPaid: Long,
    val items: List<SaleItemRequest> = emptyList(),
    val discountAmount: Long = 0L,
    val taxAmount: Long = 0L,
    val customerId: String? = null
)

@Serializable
data class CashPaymentLegacyRequest(
    val sale: LegacySaleRequest,
    val amountPaid: Long
)

@Serializable
data class LegacySaleRequest(
    val transactionId: String,
    val cashierId: String,
    val customerId: String? = null,
    val items: List<LegacyItemRequest>,
    val discountAmount: Long = 0L
)

@Serializable
data class LegacyItemRequest(
    val itemId: String,
    val quantity: Int
)

@Serializable
data class PaymentResponse(
    val paymentId: String,
    val transactionId: String,
    val method: String,
    val subtotal: Long,
    val discountAmount: Long,
    val total: Long,
    val amountPaid: Long,
    val changeAmount: Long,
    val status: String,
    val timestamp: String
)

