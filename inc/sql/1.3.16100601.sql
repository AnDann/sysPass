-- To 1.3.16100601
ALTER TABLE `accHistory`
  CHANGE COLUMN `acchistory_userId` `acchistory_userId` SMALLINT(5) UNSIGNED NOT NULL,
  CHANGE COLUMN `acchistory_userEditId` `acchistory_userEditId` SMALLINT(5) UNSIGNED NOT NULL,
  CHANGE COLUMN `acchistory_customerId` `acchistory_customerId` INT(10) UNSIGNED NOT NULL,
  CHANGE COLUMN `acchistory_categoryId` `acchistory_categoryId` SMALLINT(5) UNSIGNED NOT NULL,
  CHANGE COLUMN `acchistory_dateEdit` `acchistory_dateEdit` DATETIME NULL DEFAULT NULL,
  ADD INDEX `fk_accHistory_users_id_idx` (`acchistory_userId` ASC),
  ADD INDEX `fk_accHistory_users_edit_id_idx` (`acchistory_userEditId` ASC),
  ADD INDEX `fk_accHistory_categories_id_idx` (`acchistory_categoryId` ASC),
  ADD INDEX `fk_accHistory_customers_id_idx` (`acchistory_customerId` ASC);

ALTER TABLE `accTags`
  CHANGE COLUMN `acctag_accountId` `acctag_accountId` SMALLINT(10) UNSIGNED NOT NULL,
  DROP INDEX `IDX_id`,
  ADD INDEX `IDX_id` (`acctag_accountId` ASC),
  ADD INDEX `fk_accTags_tags_id_idx` (`acctag_tagId` ASC);

ALTER TABLE `accUsers`
  DROP COLUMN `accuser_id`,
  CHANGE COLUMN `accuser_accountId` `accuser_accountId` SMALLINT(5) UNSIGNED NOT NULL,
  CHANGE COLUMN `accuser_userId` `accuser_userId` SMALLINT(5) UNSIGNED NOT NULL,
  ADD INDEX `fk_accUsers_users_id_idx` (`accuser_userId` ASC),
  DROP PRIMARY KEY;

ALTER TABLE `accounts`
  CHANGE COLUMN `account_id` `account_id` SMALLINT(5) UNSIGNED NOT NULL,
  CHANGE COLUMN `account_userId` `account_userId` SMALLINT(5) UNSIGNED NOT NULL,
  CHANGE COLUMN `account_userEditId` `account_userEditId` SMALLINT(5) UNSIGNED NOT NULL,
  CHANGE COLUMN `account_categoryId` `account_categoryId` SMALLINT(3) UNSIGNED NOT NULL,
  CHANGE COLUMN `account_dateEdit` `account_dateEdit` DATETIME NULL DEFAULT NULL,
  ADD INDEX `fk_accounts_user_id_idx` (`account_userId` ASC),
  ADD INDEX `fk_accounts_user__edit_id_idx` (`account_userEditId` ASC);

ALTER TABLE `authTokens`
  CHANGE COLUMN `authtoken_userId` `authtoken_userId` SMALLINT(5) UNSIGNED NOT NULL,
  ADD INDEX `fk_authTokens_users_id_idx` (`authtoken_userId` ASC, `authtoken_createdBy` ASC);

ALTER TABLE `log`
  CHANGE COLUMN `log_userId` `log_userId` SMALLINT(5) UNSIGNED NOT NULL,
  CHANGE COLUMN `log_description` `log_description` TEXT NULL DEFAULT NULL,
  ADD INDEX `fk_log_users_id_idx` (`log_userId` ASC);

ALTER TABLE `usrData`
  CHANGE COLUMN `user_groupId` `user_groupId` SMALLINT(3) UNSIGNED NOT NULL,
  CHANGE COLUMN `user_secGroupId` `user_secGroupId` SMALLINT(3) UNSIGNED NULL DEFAULT NULL,
  CHANGE COLUMN `user_profileId` `user_profileId` SMALLINT(5) UNSIGNED NOT NULL,
  CHANGE COLUMN `user_isAdminApp` `user_isAdminApp` BIT(1) NULL DEFAULT b'0',
  CHANGE COLUMN `user_isAdminAcc` `user_isAdminAcc` BIT(1) NULL DEFAULT b'0',
  CHANGE COLUMN `user_isLdap` `user_isLdap` BIT(1) NULL DEFAULT b'0',
  CHANGE COLUMN `user_isDisabled` `user_isDisabled` BIT(1) NULL DEFAULT b'0',
  ADD INDEX `fk_usrData_groups_id_idx` (`user_groupId` ASC),
  ADD INDEX `fk_usrData_profiles_id_idx` (`user_profileId` ASC);

ALTER TABLE `usrPassRecover`
  CHANGE COLUMN `userpassr_used` `userpassr_used` BIT(1) NULL DEFAULT b'0';

ALTER TABLE `usrToGroups`
  DROP COLUMN `usertogroup_id`,
  CHANGE COLUMN `usertogroup_userId` `usertogroup_userId` SMALLINT(5) UNSIGNED NOT NULL,
  CHANGE COLUMN `usertogroup_groupId` `usertogroup_groupId` SMALLINT(5) UNSIGNED NOT NULL,
  ADD INDEX `fk_usrToGroups_groups_id_idx` (`usertogroup_groupId` ASC),
  DROP PRIMARY KEY;

ALTER TABLE `accFavorites`
  ADD CONSTRAINT `fk_accFavorites_accounts_id`
FOREIGN KEY (`accfavorite_accountId`)
REFERENCES `accounts` (`account_id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_accFavorites_users_id`
FOREIGN KEY (`accfavorite_userId`)
REFERENCES `usrData` (`user_id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;

ALTER TABLE `accFiles`
  ADD CONSTRAINT `fk_accFiles_accounts_id`
FOREIGN KEY (`accfile_accountId`)
REFERENCES `accounts` (`account_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `accGroups`
  ADD CONSTRAINT `fk_accGroups_accounts_id`
FOREIGN KEY (`accgroup_accountId`)
REFERENCES `accounts` (`account_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_accGroups_groups_id`
FOREIGN KEY (`accgroup_groupId`)
REFERENCES `usrGroups` (`usergroup_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `accHistory`
  ADD CONSTRAINT `fk_accHistory_users_id`
FOREIGN KEY (`acchistory_userId`)
REFERENCES `usrData` (`user_id`)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_accHistory_users_edit_id`
FOREIGN KEY (`acchistory_userEditId`)
REFERENCES `usrData` (`user_id`)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_accHistory_categories_id`
FOREIGN KEY (`acchistory_categoryId`)
REFERENCES `categories` (`category_id`)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_accHistory_customers_id`
FOREIGN KEY (`acchistory_customerId`)
REFERENCES `customers` (`customer_id`)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;

ALTER TABLE `accTags`
  ADD CONSTRAINT `fk_accTags_accounts_id`
FOREIGN KEY (`acctag_accountId`)
REFERENCES `accounts` (`account_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_accTags_tags_id`
FOREIGN KEY (`acctag_tagId`)
REFERENCES `tags` (`tag_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `accUsers`
  ADD CONSTRAINT `fk_accUsers_accounts_id`
FOREIGN KEY (`accuser_accountId`)
REFERENCES `accounts` (`account_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_accUsers_users_id`
FOREIGN KEY (`accuser_userId`)
REFERENCES `usrData` (`user_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `accounts`
  ADD CONSTRAINT `fk_accounts_categories_id`
FOREIGN KEY (`account_categoryId`)
REFERENCES `categories` (`category_id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_accounts_user_id`
FOREIGN KEY (`account_userId`)
REFERENCES `usrData` (`user_id`)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_accounts_user_edit_id`
FOREIGN KEY (`account_userEditId`)
REFERENCES `usrData` (`user_id`)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_accounts_customer_id`
FOREIGN KEY (`account_customerId`)
REFERENCES `customers` (`customer_id`)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;

ALTER TABLE `authTokens`
  ADD CONSTRAINT `fk_authTokens_users_id`
FOREIGN KEY (`authtoken_userId`, `authtoken_createdBy`)
REFERENCES `usrData` (`user_id`, `user_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `customFieldsData`
  ADD CONSTRAINT `fk_customFieldsData_def_id`
FOREIGN KEY (`customfielddata_defId`)
REFERENCES `customFieldsDef` (`customfielddef_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `log`
  ADD CONSTRAINT `fk_log_users_id`
FOREIGN KEY (`log_userId`)
REFERENCES `usrData` (`user_id`)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;

ALTER TABLE `usrData`
  ADD CONSTRAINT `fk_usrData_groups_id`
FOREIGN KEY (`user_groupId`)
REFERENCES `usrGroups` (`usergroup_id`)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_usrData_profiles_id`
FOREIGN KEY (`user_profileId`)
REFERENCES `usrProfiles` (`userprofile_id`)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;

ALTER TABLE `usrPassRecover`
  ADD CONSTRAINT `fk_usrPassRecover_users`
FOREIGN KEY (`userpassr_userId`)
REFERENCES `usrData` (`user_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `usrToGroups`
  ADD CONSTRAINT `fk_usrToGroups_users_id`
FOREIGN KEY (`usertogroup_userId`)
REFERENCES `usrData` (`user_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usrToGroups_groups_id`
FOREIGN KEY (`usertogroup_groupId`)
REFERENCES `usrGroups` (`usergroup_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `accounts`
  ADD COLUMN `account_isPrivate` BIT(1) NULL DEFAULT b'0'
  AFTER `account_otherUserEdit`;

ALTER TABLE `accounts`
  ADD COLUMN `account_passDate` INT UNSIGNED NULL AFTER `account_isPrivate`,
  ADD COLUMN `account_passDateChange` INT UNSIGNED NULL AFTER `account_passDate`;

ALTER TABLE `accHistory`
  ADD COLUMN `accHistory_passDate` INT UNSIGNED NULL AFTER `accHistory_otherGroupEdit`,
  ADD COLUMN `accHistory_passDateChange` INT UNSIGNED NULL AFTER `accHistory_passDate`;

CREATE ALGORITHM=UNDEFINED DEFINER = CURRENT_USER SQL SECURITY DEFINER VIEW `account_data_v` AS select `accounts`.`account_id` AS `account_id`,`accounts`.`account_name` AS `account_name`,`accounts`.`account_categoryId` AS `account_categoryId`,`accounts`.`account_userId` AS `account_userId`,`accounts`.`account_customerId` AS `account_customerId`,`accounts`.`account_userGroupId` AS `account_userGroupId`,`accounts`.`account_userEditId` AS `account_userEditId`,`accounts`.`account_login` AS `account_login`,`accounts`.`account_url` AS `account_url`,`accounts`.`account_notes` AS `account_notes`,`accounts`.`account_countView` AS `account_countView`,`accounts`.`account_countDecrypt` AS `account_countDecrypt`,`accounts`.`account_dateAdd` AS `account_dateAdd`,`accounts`.`account_dateEdit` AS `account_dateEdit`,`accounts`.`account_otherUserEdit` AS `account_otherUserEdit`,`accounts`.`account_otherGroupEdit` AS `account_otherGroupEdit`,`accounts`.`account_isPrivate` AS `account_isPrivate`,`accounts`.`account_passDate` AS `account_passDate`,`accounts`.`account_passDateChange` AS `account_passDateChange`,`categories`.`category_name` AS `category_name`,`customers`.`customer_name` AS `customer_name`,`ug`.`usergroup_name` AS `usergroup_name`,`u1`.`user_name` AS `user_name`,`u1`.`user_login` AS `user_login`,`u2`.`user_name` AS `user_editName`,`u2`.`user_login` AS `user_editLogin`,`publicLinks`.`publicLink_hash` AS `publicLink_hash` from ((((((`accounts` left join `categories` on((`accounts`.`account_categoryId` = `categories`.`category_id`))) left join `usrGroups` `ug` on((`accounts`.`account_userGroupId` = `ug`.`usergroup_id`))) left join `usrData` `u1` on((`accounts`.`account_userId` = `u1`.`user_id`))) left join `usrData` `u2` on((`accounts`.`account_userEditId` = `u2`.`user_id`))) left join `customers` on((`accounts`.`account_customerId` = `customers`.`customer_id`))) left join `publicLinks` on((`accounts`.`account_id` = `publicLinks`.`publicLink_itemId`)));

CREATE ALGORITHM=UNDEFINED DEFINER = CURRENT_USER SQL SECURITY DEFINER VIEW `account_search_v` AS select distinct `accounts`.`account_id` AS `account_id`,`accounts`.`account_customerId` AS `account_customerId`,`accounts`.`account_categoryId` AS `account_categoryId`,`accounts`.`account_name` AS `account_name`,`accounts`.`account_login` AS `account_login`,`accounts`.`account_url` AS `account_url`,`accounts`.`account_notes` AS `account_notes`,`accounts`.`account_userId` AS `account_userId`,`accounts`.`account_userGroupId` AS `account_userGroupId`,`accounts`.`account_otherUserEdit` AS `account_otherUserEdit`,`accounts`.`account_otherGroupEdit` AS `account_otherGroupEdit`,`accounts`.`account_isPrivate` AS `account_isPrivate`,`accounts`.`account_passDate` AS `account_passDate`,`accounts`.`account_passDateChange` AS `account_passDateChange`,`ug`.`usergroup_name` AS `usergroup_name`,`categories`.`category_name` AS `category_name`,`customers`.`customer_name` AS `customer_name`,(select count(0) from `accFiles` where (`accFiles`.`accfile_accountId` = `accounts`.`account_id`)) AS `num_files` from (((`accounts` left join `categories` on((`accounts`.`account_categoryId` = `categories`.`category_id`))) left join `usrGroups` `ug` on((`accounts`.`account_userGroupId` = `ug`.`usergroup_id`))) left join `customers` on((`customers`.`customer_id` = `accounts`.`account_customerId`)));