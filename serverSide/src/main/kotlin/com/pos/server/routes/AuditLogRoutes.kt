package com.pos.server.routes

import com.pos.server.plugins.requireAuth
import com.pos.server.service.AuditLogService
import com.pos.server.service.AuthService
import io.ktor.http.HttpStatusCode
import io.ktor.server.application.call
import io.ktor.server.response.respond
import io.ktor.server.routing.Route
import io.ktor.server.routing.get
import io.ktor.server.routing.route

fun Route.auditLogRoutes(auditLogService: AuditLogService, authService: AuthService) {
    route("/audit-logs") {
        get {
            // Memerlukan otorisasi role ADMIN untuk melihat log audit NoSQL
            val user = call.requireAuth(authService, listOf("ADMIN")) ?: return@get
            val limit = call.request.queryParameters["limit"]?.toIntOrNull() ?: 50
            val logs = auditLogService.getRecentLogs(limit)
            call.respond(HttpStatusCode.OK, logs)
        }
    }
}

