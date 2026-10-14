package com.pos.server.dto.customer

import kotlinx.serialization.Serializable

@Serializable
data class CustomerDto(
    val id: String,
    val name: String,
    val contact: String?,
    val isMember: Boolean,
    val memberNumber: String? = null,
    val points: Int = 0,
    val tier: String? = null
)

@Serializable
data class CreateCustomerRequest(
    val name: String,
    val contact: String? = null,
    val asMember: Boolean = false
)

@Serializable
data class UpdateCustomerRequest(
    val name: String? = null,
    val contact: String? = null
)

