package com.pos.server.plugins

import com.pos.server.dto.auth.UserResponse
import com.pos.server.dto.common.ApiErrorResponse
import com.pos.server.service.AuthService
import io.ktor.http.HttpStatusCode
import io.ktor.server.application.Application
import io.ktor.server.application.ApplicationCall
import io.ktor.server.response.respond
import java.util.concurrent.ConcurrentHashMap

object TokenManager {
    private val sessions = ConcurrentHashMap<String, UserResponse>()

    fun registerSession(token: String, user: UserResponse) {
        sessions[token] = user
    }

    fun findUser(token: String?, authService: AuthService? = null): UserResponse? {
        if (token.isNullOrBlank()) return null
        val clean = if (token.startsWith("Bearer ", ignoreCase = true)) {
            token.substring(7).trim()
        } else token.trim()

        // Validasi ketat: Hanya token acak yang terdaftar dalam sesi valid yang diizinkan
        return sessions[clean]
    }

    fun revoke(token: String?): Boolean {
        if (token.isNullOrBlank()) return false
        val clean = if (token.startsWith("Bearer ", ignoreCase = true)) {
            token.substring(7).trim()
        } else token.trim()
        return sessions.remove(clean) != null
    }

    fun clearAll() {
        sessions.clear()
    }
}

fun Application.configureSecurity() {
    // Keamanan RBAC dan token terpasang via TokenManager
}

/**
 * Helper verifikasi autentikasi dan peran (Role-Based Access Control).
 */
suspend fun ApplicationCall.requireAuth(
    authService: AuthService,
    allowedRoles: List<String> = emptyList()
): UserResponse? {
    val authHeader = request.headers["Authorization"]
        ?: request.headers["X-Auth-Token"]

    val user = TokenManager.findUser(authHeader, authService)
    if (user == null) {
        respond(
            HttpStatusCode.Unauthorized,
            ApiErrorResponse("UNAUTHORIZED", "Sesi autentikasi tidak valid atau kedaluwarsa. Silakan login kembali.")
        )
        return null
    }

    if (!user.active) {
        respond(
            HttpStatusCode.Forbidden,
            ApiErrorResponse("ACCOUNT_DISABLED", "Akun Anda dinonaktifkan. Silakan hubungi Administrator.")
        )
        return null
    }

    if (allowedRoles.isNotEmpty() && !allowedRoles.contains(user.role.uppercase())) {
        respond(
            HttpStatusCode.Forbidden,
            ApiErrorResponse("FORBIDDEN", "Akses ditolak: Peran '${user.role}' tidak memiliki izin untuk fitur ini.")
        )
        return null
    }

    return user
}
