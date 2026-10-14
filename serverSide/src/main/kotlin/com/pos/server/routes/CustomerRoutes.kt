package com.pos.server.routes

import com.pos.server.domain.model.Member
import com.pos.server.dto.common.ApiErrorResponse
import com.pos.server.dto.customer.CreateCustomerRequest
import com.pos.server.dto.customer.CustomerDto
import com.pos.server.dto.customer.UpdateCustomerRequest
import com.pos.server.service.CustomerService
import io.ktor.http.HttpStatusCode
import io.ktor.server.application.call
import io.ktor.server.request.receive
import io.ktor.server.response.respond
import io.ktor.server.routing.Route
import io.ktor.server.routing.get
import io.ktor.server.routing.patch
import io.ktor.server.routing.post
import io.ktor.server.routing.route

fun Route.customerRoutes(customerService: CustomerService) {
    route("/customers") {
        get {
            val query = call.request.queryParameters["query"]
            val customers = customerService.getAllCustomers(query).map {
                CustomerDto(
                    id = it.id,
                    name = it.nama,
                    contact = it.kontak,
                    isMember = it.isMember,
                    memberNumber = if (it is Member) it.nomorMember else null,
                    points = if (it is Member) it.poin else 0,
                    tier = if (it is Member) it.tingkatMember.name else null
                )
            }
            call.respond(HttpStatusCode.OK, customers)
        }

        post {
            try {
                val req = call.receive<CreateCustomerRequest>()
                val customer = customerService.registerCustomer(req)
                call.respond(
                    HttpStatusCode.Created,
                    CustomerDto(
                        id = customer.id,
                        name = customer.nama,
                        contact = customer.kontak,
                        isMember = customer.isMember,
                        memberNumber = if (customer is Member) customer.nomorMember else null,
                        points = if (customer is Member) customer.poin else 0,
                        tier = if (customer is Member) customer.tingkatMember.name else null
                    )
                )
            } catch (e: Exception) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Gagal mendaftarkan pelanggan."))
            }
        }

        get("/{id}") {
            try {
                val id = call.parameters["id"] ?: throw IllegalArgumentException("ID pelanggan diperlukan.")
                val customer = customerService.getCustomerById(id)
                call.respond(
                    HttpStatusCode.OK,
                    CustomerDto(
                        id = customer.id,
                        name = customer.nama,
                        contact = customer.kontak,
                        isMember = customer.isMember,
                        memberNumber = if (customer is Member) customer.nomorMember else null,
                        points = if (customer is Member) customer.poin else 0,
                        tier = if (customer is Member) customer.tingkatMember.name else null
                    )
                )
            } catch (e: Exception) {
                call.respond(HttpStatusCode.NotFound, ApiErrorResponse("NOT_FOUND", e.message ?: "Pelanggan tidak ditemukan."))
            }
        }

        patch("/{id}") {
            try {
                val role = call.request.headers["X-User-Role"] ?: "CASHIER"
                if (role.uppercase() != "ADMIN") {
                    call.respond(
                        HttpStatusCode.Forbidden,
                        ApiErrorResponse("FORBIDDEN", "Hanya Administrator yang memiliki hak mengubah data pelanggan.")
                    )
                    return@patch
                }

                val id = call.parameters["id"] ?: throw IllegalArgumentException("ID pelanggan diperlukan.")
                val req = call.receive<UpdateCustomerRequest>()
                val updated = customerService.updateCustomer(id, req)
                call.respond(
                    HttpStatusCode.OK,
                    CustomerDto(
                        id = updated.id,
                        name = updated.nama,
                        contact = updated.kontak,
                        isMember = updated.isMember,
                        memberNumber = if (updated is Member) updated.nomorMember else null,
                        points = if (updated is Member) updated.poin else 0,
                        tier = if (updated is Member) updated.tingkatMember.name else null
                    )
                )
            } catch (e: Exception) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Gagal mengubah data pelanggan."))
            }
        }
    }
}

