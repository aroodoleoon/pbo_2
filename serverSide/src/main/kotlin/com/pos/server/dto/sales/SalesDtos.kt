package com.pos.server.dto.sales

import kotlinx.serialization.Serializable

@Serializable
data class SaleItemRequest(
    val productId: String,
    val quantity: Int
)

@Serializable
data class CreateSaleRequest(
    val transactionId: String? = null,
    val customerId: String? = null,
    val items: List<SaleItemRequest>,
    val discountAmount: Long = 0L
)

@Serializable
data class SaleItemResponse(
    val id: String,
    val productId: String,
    val productName: String,
    val quantity: Int,
    val unitPrice: Long,
    val subtotal: Long
)

@Serializable
data class SaleResponse(
    val id: String,
    val transactionNumber: String,
    val cashierId: String,
    val customerId: String?,
    val subtotal: Long,
    val discount: Long,
    val tax: Long,
    val total: Long,
    val discountDescription: String?,
    val status: String,
    val createdAt: String,
    val items: List<SaleItemResponse> = emptyList()
)

