ALTER TABLE llx_collabtrack_presence ADD UNIQUE KEY history (entity,fk_user,element_id,element_type,action_edit);
