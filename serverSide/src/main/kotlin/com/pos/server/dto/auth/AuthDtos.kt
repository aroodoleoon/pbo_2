package com.pos.server.dto.auth

import kotlinx.serialization.Serializable

@Serializable
data class LoginRequest(
    val username: String,
    val password: String
)

@Serializable
data class UserResponse(
    val id: String,
    val name: String,
    val username: String,
    val role: String,
    val active: Boolean
)

@Serializable
data class LoginResponse(
    val accessToken: String,
    val user: UserResponse
)

@Serializable
data class CreateEmployeeRequest(
    val name: String,
    val username: String,
    val password: String,
    val role: String = "CASHIER"
)


