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

fun Route.salesRoutes(
    salesService: SalesService,
    paymentService: PaymentService,
    auditLogService: com.pos.server.service.AuditLogService? = null
) {
    route("/sales") {
        post("/checkout") {
            val cashierId = call.request.headers["X-User-Id"] ?: "CASHIER-01"
            try {
                val req = call.receive<com.pos.server.dto.sales.CheckoutRequest>()
                val res = paymentService.checkout(cashierId, req)
                auditLogService?.log(
                    actorId = cashierId,
                    action = "CHECKOUT",
                    entityType = "TRANSACTION",
                    entityId = res.transactionId,
                    result = "SUCCESS",
                    metadata = """{"invoiceNumber":"${res.transactionNumber}","totalAmount":${res.total},"paymentMethod":"${res.payment?.method ?: ""}"}"""
                )
                call.respond(HttpStatusCode.Created, res)
            } catch (e: com.pos.server.domain.exception.StokTidakCukupException) {
                auditLogService?.log(
                    actorId = cashierId,
                    action = "CHECKOUT",
                    entityType = "TRANSACTION",
                    entityId = null,
                    result = "FAILED_OUT_OF_STOCK",
                    metadata = """{"reason":"${e.message}"}"""
                )
                call.respond(HttpStatusCode.Conflict, ApiErrorResponse("OUT_OF_STOCK", e.message ?: "Stok barang tidak mencukupi."))
            } catch (e: com.pos.server.domain.exception.ValidasiException) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Validasi checkout gagal."))
            } catch (e: IllegalArgumentException) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Data checkout tidak valid."))
            } catch (e: Exception) {
                call.respond(HttpStatusCode.InternalServerError, ApiErrorResponse("SERVER_ERROR", e.message ?: "Gagal memproses checkout."))
            }
        }

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
                    customerId = req.customerId,
                    items = req.items,
                    discountAmount = req.discountAmount,
                    taxAmount = req.taxAmount,
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

