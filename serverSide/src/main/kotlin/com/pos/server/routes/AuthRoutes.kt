package com.pos.server.routes

import com.pos.server.dto.auth.CreateEmployeeRequest
import com.pos.server.dto.auth.LoginRequest
import com.pos.server.dto.common.ApiErrorResponse
import com.pos.server.plugins.requireAuth
import com.pos.server.service.AuthService
import io.ktor.http.HttpStatusCode
import io.ktor.server.application.call
import io.ktor.server.request.receive
import io.ktor.server.response.respond
import io.ktor.server.routing.Route
import io.ktor.server.routing.delete
import io.ktor.server.routing.get
import io.ktor.server.routing.post
import io.ktor.server.routing.route

fun Route.authRoutes(authService: AuthService, auditLogService: com.pos.server.service.AuditLogService? = null) {
    route("/auth") {
        post("/login") {
            var attemptedUsername = ""
            try {
                val req = call.receive<LoginRequest>()
                attemptedUsername = req.username
                val res = authService.login(req)
                auditLogService?.log(
                    actorId = res.user.id,
                    action = "LOGIN",
                    entityType = "AUTH",
                    entityId = res.user.id,
                    result = "SUCCESS",
                    metadata = """{"username":"${req.username}","role":"${res.user.role}"}"""
                )
                call.respond(HttpStatusCode.OK, res)
            } catch (e: Exception) {
                auditLogService?.log(
                    actorId = null,
                    action = "LOGIN",
                    entityType = "AUTH",
                    entityId = attemptedUsername.ifBlank { "unknown" },
                    result = "FAILED",
                    metadata = """{"reason":"${e.message}"}"""
                )
                call.respond(
                    HttpStatusCode.Unauthorized,
                    ApiErrorResponse("UNAUTHORIZED", e.message ?: "Login gagal.")
                )
            }
        }

        post("/logout") {
            val authHeader = call.request.headers[io.ktor.http.HttpHeaders.Authorization]
                ?: call.request.headers["X-Auth-Token"]
            val user = com.pos.server.plugins.TokenManager.findUser(authHeader)
            val revoked = com.pos.server.plugins.TokenManager.revoke(authHeader)
            if (user != null) {
                auditLogService?.log(
                    actorId = user.id,
                    action = "LOGOUT",
                    entityType = "AUTH",
                    entityId = user.id,
                    result = "SUCCESS",
                    metadata = """{"username":"${user.username}","revoked":$revoked}"""
                )
            }
            call.respond(HttpStatusCode.OK, mapOf("message" to "Berhasil keluar dari sesi."))
        }
    }

    route("/employees") {
        get {
            val user = call.requireAuth(authService, listOf("ADMIN")) ?: return@get
            val list = authService.getAllEmployees()
            call.respond(HttpStatusCode.OK, list)
        }

        post {
            val user = call.requireAuth(authService, listOf("ADMIN")) ?: return@post
            try {
                val req = call.receive<CreateEmployeeRequest>()
                val res = authService.createEmployee(req)
                call.respond(HttpStatusCode.Created, res)
            } catch (e: IllegalArgumentException) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Validasi gagal."))
            } catch (e: Exception) {
                call.respond(HttpStatusCode.InternalServerError, ApiErrorResponse("SERVER_ERROR", e.message ?: "Gagal menambah karyawan."))
            }
        }

        post("/{id}/toggle") {
            val user = call.requireAuth(authService, listOf("ADMIN")) ?: return@post
            try {
                val id = call.parameters["id"] ?: return@post call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", "ID diperlukan."))
                val res = authService.toggleStatus(id)
                call.respond(HttpStatusCode.OK, res)
            } catch (e: Exception) {
                call.respond(HttpStatusCode.NotFound, ApiErrorResponse("NOT_FOUND", e.message ?: "Karyawan tidak ditemukan."))
            }
        }

        delete("/{id}") {
            val user = call.requireAuth(authService, listOf("ADMIN")) ?: return@delete
            try {
                val id = call.parameters["id"] ?: return@delete call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", "ID diperlukan."))
                authService.deleteEmployee(id)
                call.respond(HttpStatusCode.OK, mapOf("message" to "Karyawan berhasil dihapus."))
            } catch (e: Exception) {
                call.respond(HttpStatusCode.InternalServerError, ApiErrorResponse("SERVER_ERROR", e.message ?: "Gagal menghapus karyawan."))
            }
        }
    }
}


