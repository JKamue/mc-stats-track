<?php

return [
    "table_data" => "CREATE TABLE `data` (
                        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                        `when` datetime DEFAULT NULL,
                        `online` mediumint(9) DEFAULT NULL,
                        `max` mediumint(9) DEFAULT NULL,
                        `server` varchar(13) DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        KEY `when` (`when`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
    "table_servers" => "CREATE TABLE `servers` (
                        `server` varchar(13) DEFAULT NULL UNIQUE,
                        `adress` text DEFAULT NULL,
                        `name` varchar(30) DEFAULT NULL,
                        `added` DATETIME DEFAULT NULL,
                        PRIMARY KEY (`server`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
    "table_links" => "alter table data add constraint fk_db_server foreign key(server) references servers(server) on update cascade on delete cascade;"
];