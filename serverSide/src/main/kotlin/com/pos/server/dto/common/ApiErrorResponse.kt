package com.pos.server.dto.common

import kotlinx.serialization.Serializable

@Serializable
data class ErrorDetail(
    val code: String,
    val message: String,
    val requestId: String? = null
)

@Serializable
data class ApiErrorResponse(
    val error: ErrorDetail
) {
    constructor(code: String, message: String, requestId: String? = null) : this(
        ErrorDetail(code, message, requestId)
    )
}

