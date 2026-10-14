package com.pos.server.domain.exception

open class DomainException(message: String, val code: String = "DOMAIN_ERROR") : RuntimeException(message)

class ValidasiException(message: String) : DomainException(message, "VALIDATION_ERROR")

class StokTidakCukupException(message: String) : DomainException(message, "INSUFFICIENT_STOCK")

class BarangTidakDitemukanException(message: String) : DomainException(message, "PRODUCT_NOT_FOUND")

class DataTidakDitemukanException(message: String) : DomainException(message, "NOT_FOUND")

class AksesDitolakException(message: String) : DomainException(message, "FORBIDDEN")

class AutentikasiException(message: String) : DomainException(message, "UNAUTHORIZED")

class PembayaranGagalException(message: String) : DomainException(message, "PAYMENT_FAILED")

