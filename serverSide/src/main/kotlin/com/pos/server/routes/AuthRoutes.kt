package com.pos.server.routes

import com.pos.server.dto.auth.LoginRequest
import com.pos.server.dto.common.ApiErrorResponse
import com.pos.server.service.AuthService
import io.ktor.http.HttpStatusCode
import io.ktor.server.application.call
import io.ktor.server.request.receive
import io.ktor.server.response.respond
import io.ktor.server.routing.Route
import io.ktor.server.routing.post
import io.ktor.server.routing.route

fun Route.authRoutes(authService: AuthService) {
    route("/auth") {
        post("/login") {
            try {
                val req = call.receive<LoginRequest>()
                val res = authService.login(req)
                call.respond(HttpStatusCode.OK, res)
            } catch (e: Exception) {
                call.respond(
                    HttpStatusCode.Unauthorized,
                    ApiErrorResponse("UNAUTHORIZED", e.message ?: "Login gagal.")
                )
            }
        }

        post("/logout") {
            call.respond(HttpStatusCode.OK, mapOf("message" to "Berhasil keluar dari sesi."))
        }
    }
}

