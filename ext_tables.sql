CREATE TABLE tx_extbaseuploadtypeconverter_domain_model_singlefile (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    sorting int(11) DEFAULT '0' NOT NULL,
    fe_group varchar(255) NOT NULL DEFAULT '0',
    editlock smallint unsigned NOT NULL DEFAULT '0',
    sys_language_uid int NOT NULL DEFAULT '0',
    l10n_parent int unsigned NOT NULL DEFAULT '0',
    l10n_source int unsigned NOT NULL DEFAULT '0',
    l10n_state text,
    t3_origuid int unsigned NOT NULL DEFAULT '0',
    l10n_diffsource mediumblob,
    t3ver_oid int unsigned NOT NULL DEFAULT '0',
    t3ver_wsid int unsigned NOT NULL DEFAULT '0',
    t3ver_state smallint NOT NULL DEFAULT '0',
    t3ver_stage int NOT NULL DEFAULT '0',

    title varchar(255) DEFAULT '' NOT NULL,
    file int(11) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);
