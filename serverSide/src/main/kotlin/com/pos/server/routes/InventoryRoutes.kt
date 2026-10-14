package com.pos.server.routes

import com.pos.server.domain.enums.StockMovementType
import com.pos.server.dto.common.ApiErrorResponse
import com.pos.server.dto.inventory.StockAdjustmentRequest
import com.pos.server.dto.inventory.StockMovementDto
import com.pos.server.dto.inventory.StockReceiptRequest
import com.pos.server.dto.product.ProductDto
import com.pos.server.service.InventoryService
import io.ktor.http.HttpStatusCode
import io.ktor.server.application.call
import io.ktor.server.request.receive
import io.ktor.server.response.respond
import io.ktor.server.routing.Route
import io.ktor.server.routing.get
import io.ktor.server.routing.post
import io.ktor.server.routing.route
import java.time.Instant

fun Route.inventoryRoutes(inventoryService: InventoryService) {
    route("/inventory") {
        post("/receipts") {
            try {
                val req = call.receive<StockReceiptRequest>()
                val employeeId = call.request.headers["X-User-Id"] ?: "SYSTEM"
                val barang = inventoryService.barangMasuk(
                    barangId = req.productId,
                    jumlah = req.quantity,
                    sumber = req.source,
                    karyawanId = employeeId,
                    alasan = req.notes
                )
                call.respond(
                    HttpStatusCode.Created,
                    ProductDto(
                        id = barang.id,
                        barcode = barang.kode,
                        name = barang.nama,
                        categoryId = barang.kategoriId,
                        buyPrice = barang.hargaBeli,
                        sellPrice = barang.hargaJual,
                        stock = barang.stok,
                        minStock = barang.stokMinimum,
                        active = barang.aktif
                    )
                )
            } catch (e: Exception) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Gagal memproses penerimaan barang."))
            }
        }

        post("/adjustments") {
            try {
                val req = call.receive<StockAdjustmentRequest>()
                val employeeId = call.request.headers["X-User-Id"] ?: "SYSTEM"
                val movementType = StockMovementType.fromString(req.type)
                val barang = inventoryService.barangKeluar(
                    barangId = req.productId,
                    jumlah = req.quantity,
                    jenis = movementType,
                    alasan = req.reason,
                    karyawanId = employeeId
                )
                call.respond(
                    HttpStatusCode.OK,
                    ProductDto(
                        id = barang.id,
                        barcode = barang.kode,
                        name = barang.nama,
                        categoryId = barang.kategoriId,
                        buyPrice = barang.hargaBeli,
                        sellPrice = barang.hargaJual,
                        stock = barang.stok,
                        minStock = barang.stokMinimum,
                        active = barang.aktif
                    )
                )
            } catch (e: Exception) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Gagal menyesuaikan stok."))
            }
        }

        get("/movements") {
            try {
                val productId = call.request.queryParameters["productId"]
                val fromStr = call.request.queryParameters["from"]
                val toStr = call.request.queryParameters["to"]

                val from = if (!fromStr.isNullOrBlank()) Instant.parse(fromStr) else null
                val to = if (!toStr.isNullOrBlank()) Instant.parse(toStr) else null

                val movements = inventoryService.riwayatPergerakan(productId, from, to).map {
                    StockMovementDto(
                        id = it.id,
                        productId = it.barangId,
                        type = it.jenis.name,
                        quantity = it.jumlah,
                        reason = it.alasan,
                        employeeId = it.karyawanId,
                        reference = it.referensi,
                        timestamp = it.dibuatPada.toString()
                    )
                }
                call.respond(HttpStatusCode.OK, movements)
            } catch (e: Exception) {
                call.respond(HttpStatusCode.BadRequest, ApiErrorResponse("BAD_REQUEST", e.message ?: "Gagal mengambil data pergerakan stok."))
            }
        }

        get("/low-stock") {
            val list = inventoryService.daftarStokMenipis().map {
                ProductDto(
                    id = it.id,
                    barcode = it.kode,
                    name = it.nama,
                    categoryId = it.kategoriId,
                    buyPrice = it.hargaBeli,
                    sellPrice = it.hargaJual,
                    stock = it.stok,
                    minStock = it.stokMinimum,
                    active = it.aktif
                )
            }
            call.respond(HttpStatusCode.OK, list)
        }
    }
}

