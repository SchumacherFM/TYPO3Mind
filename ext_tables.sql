#
# Table structure for table 'tx_typo3mind_domain_model_t3mind'
#
CREATE TABLE tx_typo3mind_domain_model_t3mind (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,


	page_uid int(11) DEFAULT '0' NOT NULL,
	encrypt tinyint(1) unsigned DEFAULT '0' NOT NULL,
	recursive tinyint(1) unsigned DEFAULT '0' NOT NULL,
	show_details_note tinyint(1) unsigned DEFAULT '0' NOT NULL,
	font_face varchar(255) DEFAULT '' NOT NULL,
	font_color varchar(7) DEFAULT '' NOT NULL,
	font_size int(11) DEFAULT '0' NOT NULL,
	font_bold tinyint(1) unsigned DEFAULT '0' NOT NULL,
	font_italic tinyint(1) unsigned DEFAULT '0' NOT NULL,
	cloud_is tinyint(1) unsigned DEFAULT '0' NOT NULL,
	cloud_color varchar(7) DEFAULT '' NOT NULL,
	node_color varchar(7) DEFAULT '' NOT NULL,
	node_folded tinyint(1) unsigned DEFAULT '0' NOT NULL,
	node_style varchar(25) DEFAULT '' NOT NULL,
	node_icon text NOT NULL,
	node_user_icon text NOT NULL,
	edge_color varchar(7) DEFAULT '' NOT NULL,
	edge_style varchar(25) DEFAULT ''  NOT NULL,
	edge_width varchar(25) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	UNIQUE  (  `page_uid` ),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)
);