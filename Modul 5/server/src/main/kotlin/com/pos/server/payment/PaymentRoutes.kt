package com.pos.server.payment

import com.pos.shared.payment.ApiError
import com.pos.shared.payment.CashPaymentRequest
import io.ktor.http.HttpStatusCode
import io.ktor.server.application.call
import io.ktor.server.request.receive
import io.ktor.server.response.respond
import io.ktor.server.routing.Route
import io.ktor.server.routing.get
import io.ktor.server.routing.post
import io.ktor.server.routing.route
import java.sql.Date
import java.time.LocalDate
import java.time.format.DateTimeParseException

fun Route.paymentRoutes(paymentService: PaymentService, repository: PosRepository) {
    route("/api") {
        post("/payments/cash") {
            try {
                call.respond(HttpStatusCode.Created, paymentService.payCash(call.receive<CashPaymentRequest>()))
            } catch (e: IllegalArgumentException) {
                call.respond(HttpStatusCode.BadRequest, ApiError(e.message ?: "Data tidak valid."))
            } catch (e: Exception) {
                call.respond(HttpStatusCode.Conflict, ApiError(e.message ?: "Pembayaran gagal."))
            }
        }
        get("/transactions") {
            try {
                val limit = call.request.queryParameters["limit"]?.toIntOrNull() ?: 100
                call.respond(repository.getTransactionHistory(limit))
            } catch (e: IllegalArgumentException) {
                call.respond(HttpStatusCode.BadRequest, ApiError(e.message ?: "Parameter tidak valid."))
            }
        }
        get("/reports/sales") {
            try {
                val from = LocalDate.parse(call.request.queryParameters["from"]
                    ?: throw IllegalArgumentException("Parameter from wajib diisi (YYYY-MM-DD)."))
                val to = LocalDate.parse(call.request.queryParameters["to"]
                    ?: throw IllegalArgumentException("Parameter to wajib diisi (YYYY-MM-DD)."))
                require(!to.isBefore(from)) { "Tanggal akhir tidak boleh sebelum tanggal awal." }
                call.respond(repository.getSalesReport(Date.valueOf(from), Date.valueOf(to.plusDays(1))))
            } catch (e: DateTimeParseException) {
                call.respond(HttpStatusCode.BadRequest, ApiError("Format tanggal harus YYYY-MM-DD."))
            } catch (e: IllegalArgumentException) {
                call.respond(HttpStatusCode.BadRequest, ApiError(e.message ?: "Parameter tidak valid."))
            }
        }
    }
}
