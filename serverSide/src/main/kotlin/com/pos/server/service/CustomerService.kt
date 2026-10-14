package com.pos.server.service

import com.pos.server.domain.exception.DataTidakDitemukanException
import com.pos.server.domain.exception.ValidasiException
import com.pos.server.domain.model.Member
import com.pos.server.domain.model.Pelanggan
import com.pos.server.dto.customer.CreateCustomerRequest
import com.pos.server.dto.customer.UpdateCustomerRequest
import com.pos.server.repository.PelangganRepository

/**
 * Layanan Pelanggan dan Member Toko (Modul 4 Dhia).
 */
class CustomerService(
    private val pelangganRepository: PelangganRepository
) {
    fun getAllCustomers(query: String? = null): List<Pelanggan> {
        return if (query.isNullOrBlank()) {
            pelangganRepository.findAll()
        } else {
            pelangganRepository.findByQuery(query)
        }
    }

    fun getCustomerById(id: String): Pelanggan {
        return pelangganRepository.findById(id)
            ?: throw DataTidakDitemukanException("Pelanggan dengan ID '$id' tidak ditemukan.")
    }

    fun registerCustomer(request: CreateCustomerRequest): Pelanggan {
        if (request.name.isBlank()) {
            throw ValidasiException("Nama pelanggan tidak boleh kosong.")
        }

        return if (request.asMember) {
            val nomorMember = "MBR-${System.currentTimeMillis() % 100000}"
            pelangganRepository.insertMember(
                nama = request.name.trim(),
                kontak = request.contact?.trim(),
                nomorMember = nomorMember
            )
        } else {
            pelangganRepository.insertPelanggan(
                nama = request.name.trim(),
                kontak = request.contact?.trim()
            )
        }
    }

    fun updateCustomer(id: String, request: UpdateCustomerRequest): Pelanggan {
        val existing = getCustomerById(id)
        pelangganRepository.updatePelanggan(id, request.name?.trim(), request.contact?.trim())
        return getCustomerById(id)
    }

    fun addLoyaltyPoints(memberNumber: String, purchaseAmount: Long) {
        val points = (purchaseAmount / 10000L).toInt()
        if (points > 0) {
            pelangganRepository.updatePoin(memberNumber, points)
        }
    }
}

