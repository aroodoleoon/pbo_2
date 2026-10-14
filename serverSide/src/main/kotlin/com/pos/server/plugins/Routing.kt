package com.pos.server.plugins

import com.pos.server.repository.*
import com.pos.server.routes.*
import com.pos.server.service.*
import io.ktor.server.application.Application
import io.ktor.server.routing.route
import io.ktor.server.routing.routing
import javax.sql.DataSource

fun Application.configureRouting(dataSource: DataSource) {
    // 1. Inisialisasi Repository
    val barangRepo = BarangRepository(dataSource)
    val kategoriRepo = KategoriRepository(dataSource)
    val pergerakanStokRepo = PergerakanStokRepository(dataSource)
    val karyawanRepo = KaryawanRepository(dataSource)
    val pelangganRepo = PelangganRepository(dataSource)
    val transaksiRepo = TransaksiRepository(dataSource)

    // 2. Inisialisasi Service (Menghubungkan Modul 2, 3, 4, 5)
    val inventoryService = InventoryService(barangRepo, pergerakanStokRepo)
    val productService = ProductService(barangRepo, kategoriRepo)
    val authService = AuthService(karyawanRepo)
    val customerService = CustomerService(pelangganRepo)
    val salesService = SalesService(barangRepo, pelangganRepo, transaksiRepo)
    val paymentService = PaymentService(transaksiRepo, inventoryService) // Integrasi StockGateway
    val reportService = ReportService(transaksiRepo)

    // 3. Routing API
    routing {
        route("/api/v1") {
            authRoutes(authService)
            productRoutes(productService)
            inventoryRoutes(inventoryService)
            customerRoutes(customerService)
            salesRoutes(salesService, paymentService)
            reportRoutes(reportService)
        }

        // Backward compatibility dengan root route /api dari Modul 5
        route("/api") {
            salesRoutes(salesService, paymentService)
            reportRoutes(reportService)
        }
    }
}

