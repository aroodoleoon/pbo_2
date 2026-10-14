package com.pos.server.plugins

import com.pos.server.domain.exception.AksesDitolakException
import com.pos.server.domain.exception.AutentikasiException
import com.pos.server.domain.exception.BarangTidakDitemukanException
import com.pos.server.domain.exception.DataTidakDitemukanException
import com.pos.server.domain.exception.StokTidakCukupException
import com.pos.server.domain.exception.ValidasiException
import com.pos.server.dto.common.ApiErrorResponse
import io.ktor.http.HttpStatusCode
import io.ktor.server.application.Application
import io.ktor.server.application.install
import io.ktor.server.plugins.statuspages.StatusPages
import io.ktor.server.response.respond

fun Application.configureStatusPages() {
    install(StatusPages) {
        exception<ValidasiException> { call, cause ->
            call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("VALIDATION_ERROR", cause.message ?: "Input tidak valid."))
        }
        exception<StokTidakCukupException> { call, cause ->
            call.respond(HttpStatusCode.Conflict, ApiErrorResponse("INSUFFICIENT_STOCK", cause.message ?: "Stok tidak mencukupi."))
        }
        exception<BarangTidakDitemukanException> { call, cause ->
            call.respond(HttpStatusCode.NotFound, ApiErrorResponse("PRODUCT_NOT_FOUND", cause.message ?: "Barang tidak ditemukan."))
        }
        exception<DataTidakDitemukanException> { call, cause ->
            call.respond(HttpStatusCode.NotFound, ApiErrorResponse("NOT_FOUND", cause.message ?: "Data tidak ditemukan."))
        }
        exception<AksesDitolakException> { call, cause ->
            call.respond(HttpStatusCode.Forbidden, ApiErrorResponse("FORBIDDEN", cause.message ?: "Akses ditolak."))
        }
        exception<AutentikasiException> { call, cause ->
            call.respond(HttpStatusCode.Unauthorized, ApiErrorResponse("UNAUTHORIZED", cause.message ?: "Autentikasi gagal."))
        }
        exception<IllegalArgumentException> { call, cause ->
            call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", cause.message ?: "Argumen tidak valid."))
        }
        exception<Throwable> { call, cause ->
            call.respond(HttpStatusCode.InternalServerError, ApiErrorResponse("INTERNAL_ERROR", cause.message ?: "Terjadi kesalahan server."))
        }
    }
}

