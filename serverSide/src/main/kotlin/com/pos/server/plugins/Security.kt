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

        // 1. Cek memory session cache
        sessions[clean]?.let { return it }

        // 2. Fallback verifikasi ke database jika server baru restart: POS-TOKEN-{id}-{uuid}
        if (clean.startsWith("POS-TOKEN-") && authService != null) {
            val raw = clean.removePrefix("POS-TOKEN-")
            // ID karyawan berformat UUID atau custom ID (cth: 22222222-2222-2222-2222-222222222222)
            val parts = raw.split("-")
            if (parts.size >= 5) {
                // Reconstruct UUID karyawan jika format UUID baku (8-4-4-4-12)
                val candidateId = parts.take(5).joinToString("-")
                try {
                    val k = authService.getKaryawanById(candidateId)
                    if (k.aktif) {
                        val user = UserResponse(k.id, k.nama, k.username, k.role.name, k.aktif)
                        sessions[clean] = user
                        return user
                    }
                } catch (_: Exception) {}
            }
        }
        return null
    }

    fun revoke(token: String?) {
        if (token.isNullOrBlank()) return
        val clean = if (token.startsWith("Bearer ", ignoreCase = true)) {
            token.substring(7).trim()
        } else token.trim()
        sessions.remove(clean)
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
