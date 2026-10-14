package com.pos.server.routes

import com.pos.server.dto.common.ApiErrorResponse
import com.pos.server.service.ReportService
import io.ktor.http.HttpStatusCode
import io.ktor.server.application.call
import io.ktor.server.response.respond
import io.ktor.server.routing.Route
import io.ktor.server.routing.get
import io.ktor.server.routing.route

fun Route.reportRoutes(reportService: ReportService) {
    route("/reports") {
        get("/sales") {
            try {
                val from = call.request.queryParameters["from"]
                    ?: throw IllegalArgumentException("Parameter 'from' wajib diisi (format: YYYY-MM-DD).")
                val to = call.request.queryParameters["to"]
                    ?: throw IllegalArgumentException("Parameter 'to' wajib diisi (format: YYYY-MM-DD).")

                val report = reportService.getSalesReport(from, to)
                call.respond(HttpStatusCode.OK, report)
            } catch (e: Exception) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Parameter tidak valid."))
            }
        }
    }

    route("/transactions") {
        get {
            try {
                val limit = call.request.queryParameters["limit"]?.toIntOrNull() ?: 100
                val history = reportService.getTransactionHistory(limit)
                call.respond(HttpStatusCode.OK, history)
            } catch (e: Exception) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Parameter tidak valid."))
            }
        }
    }
}

