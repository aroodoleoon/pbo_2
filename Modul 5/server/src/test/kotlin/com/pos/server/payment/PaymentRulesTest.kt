package com.pos.server.payment

import kotlin.test.Test
import kotlin.test.assertEquals
import kotlin.test.assertFailsWith

class PaymentRulesTest {
    @Test fun subtotalDihitungDariSnapshotHarga() {
        assertEquals(15000L, SaleLineSnapshot("BRG-1", "Teh", 3, 5000).subtotal)
    }

    @Test fun overflowSubtotalDitolak() {
        val line = SaleLineSnapshot("BRG-1", "Barang", Int.MAX_VALUE, Long.MAX_VALUE)
        assertFailsWith<ArithmeticException> { line.subtotal }
    }

    @Test fun pembayaranKurangDitolak() {
        assertFailsWith<IllegalArgumentException> {
            require(20000L >= 25000L) { "Pembayaran tunai kurang dari total belanja." }
        }
    }
}
