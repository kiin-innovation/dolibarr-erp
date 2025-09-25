
CREATE TABLE llx_collabtrack_presence (
    rowid int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    tms timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    entity int(11) NOT NULL DEFAULT 1,
    fk_user int(11) NOT NULL,
    element_id int(11) NOT NULL,
    element_type varchar(64) NOT NULL,
    action_edit tinyint(4) NOT NULL,
    date_last_view timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB
