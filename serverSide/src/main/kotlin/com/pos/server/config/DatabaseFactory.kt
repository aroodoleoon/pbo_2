package com.pos.server.config

import org.postgresql.ds.PGSimpleDataSource
import javax.sql.DataSource

object DatabaseFactory {
    fun createDataSource(config: DatabaseConfig): DataSource {
        val ds = PGSimpleDataSource()
        ds.setURL(config.url)
        ds.user = config.user
        ds.password = config.pass
        return ds
    }
}

