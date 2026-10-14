package com.pos.server.dto.product

import kotlinx.serialization.Serializable

@Serializable
data class CategoryDto(
    val id: String,
    val name: String
)

@Serializable
data class CreateCategoryRequest(
    val name: String
)

@Serializable
data class ProductDto(
    val id: String,
    val barcode: String,
    val name: String,
    val categoryId: String?,
    val buyPrice: Long,
    val sellPrice: Long,
    val stock: Int,
    val minStock: Int,
    val active: Boolean
)

@Serializable
data class CreateProductRequest(
    val barcode: String,
    val name: String,
    val categoryId: String? = null,
    val buyPrice: Long,
    val sellPrice: Long,
    val initialStock: Int = 0,
    val minStock: Int = 0
)

@Serializable
data class UpdateProductRequest(
    val name: String? = null,
    val categoryId: String? = null,
    val buyPrice: Long? = null,
    val sellPrice: Long? = null,
    val minStock: Int? = null,
    val active: Boolean? = null
)

