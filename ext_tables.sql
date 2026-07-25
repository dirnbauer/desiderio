
#
# Records where every collection child row moved during the shared-record-type
# migration. It is what makes the reference remap possible and the migration
# re-runnable, and it is the audit trail if a row is ever questioned, so it is
# deliberately kept after the migration rather than dropped.
#
CREATE TABLE tx_desiderio_collection_uid_map (
	source_table varchar(64) DEFAULT '' NOT NULL,
	source_uid int(11) unsigned DEFAULT '0' NOT NULL,
	target_table varchar(64) DEFAULT '' NOT NULL,
	target_uid int(11) unsigned DEFAULT '0' NOT NULL,

	KEY source (source_table,source_uid),
	KEY target (target_table,target_uid)
);
