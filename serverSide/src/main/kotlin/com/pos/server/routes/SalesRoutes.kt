package com.pos.server.routes

import com.pos.server.dto.common.ApiErrorResponse
import com.pos.server.dto.sales.CashPaymentLegacyRequest
import com.pos.server.dto.sales.CreateSaleRequest
import com.pos.server.dto.sales.ProcessPaymentRequest
import com.pos.server.service.PaymentService
import com.pos.server.service.SalesService
import io.ktor.http.HttpStatusCode
import io.ktor.server.application.call
import io.ktor.server.request.receive
import io.ktor.server.response.respond
import io.ktor.server.routing.Route
import io.ktor.server.routing.post
import io.ktor.server.routing.route

fun Route.salesRoutes(salesService: SalesService, paymentService: PaymentService) {
    route("/sales") {
        post {
            try {
                val cashierId = call.request.headers["X-User-Id"] ?: "CASHIER-01"
                val req = call.receive<CreateSaleRequest>()
                val res = salesService.createSale(cashierId, req)
                call.respond(HttpStatusCode.Created, res)
            } catch (e: Exception) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Gagal membuat transaksi."))
            }
        }

        post("/{id}/payments") {
            try {
                val transactionId = call.parameters["id"] ?: throw IllegalArgumentException("ID transaksi diperlukan.")
                val cashierId = call.request.headers["X-User-Id"] ?: "CASHIER-01"
                val req = call.receive<ProcessPaymentRequest>()

                // Menggunakan payload detail transaksi jika disediakan, atau default pembayaran
                val paymentResponse = paymentService.processPayment(
                    transactionId = transactionId,
                    cashierId = cashierId,
                    customerId = null,
                    items = emptyList(),
                    discountAmount = 0L,
                    taxAmount = 0L,
                    request = req
                )
                call.respond(HttpStatusCode.Created, paymentResponse)
            } catch (e: Exception) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("PAYMENT_FAILED", e.message ?: "Pembayaran gagal."))
            }
        }
    }

    // Kompatibilitas langsung dengan endpoint Modul 5 Josua
    route("/payments") {
        post("/cash") {
            try {
                val req = call.receive<CashPaymentLegacyRequest>()
                val result = paymentService.payCashLegacy(req)
                call.respond(HttpStatusCode.Created, result)
            } catch (e: IllegalArgumentException) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Data tidak valid."))
            } catch (e: Exception) {
                call.respond(HttpStatusCode.Conflict, ApiErrorResponse("CONFLICT", e.message ?: "Pembayaran gagal."))
            }
        }
    }
}

