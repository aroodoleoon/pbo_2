package com.pos.server.dto.sales

import kotlinx.serialization.Serializable

@Serializable
data class CheckoutItemDto(
    val productId: String,
    val quantity: Int
)

@Serializable
data class CheckoutPaymentDto(
    val method: String,
    val amountPaid: Long
)

@Serializable
data class CheckoutRequest(
    val idempotencyKey: String? = null,
    val customerId: String? = null,
    val items: List<CheckoutItemDto>,
    val payment: CheckoutPaymentDto
)

@Serializable
data class CheckoutItemResponseDto(
    val productId: String,
    val productName: String,
    val quantity: Int,
    val price: Long,
    val subtotal: Long
)

@Serializable
data class CheckoutPaymentResponseDto(
    val method: String,
    val amountPaid: Long,
    val change: Long,
    val status: String
)

@Serializable
data class CheckoutResponse(
    val transactionId: String,
    val transactionNumber: String,
    val status: String,
    val subtotal: Long,
    val discount: Long,
    val tax: Long,
    val total: Long,
    val amountPaid: Long,
    val change: Long,
    val items: List<CheckoutItemResponseDto> = emptyList(),
    val payment: CheckoutPaymentResponseDto? = null,
    val createdAt: String? = null
)

