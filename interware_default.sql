# Dumping database structure for interware
#CREATE DATABASE IF NOT EXISTS `interware` /*!40100 DEFAULT CHARACTER SET latin1 */;
#USE `interware`;

# Dumping structure for table interware.campaign
CREATE TABLE IF NOT EXISTS `campaign` (
  `id` int(11) NOT NULL auto_increment COMMENT 'unique campaign_id',
  `name` varchar(255) NOT NULL COMMENT 'name of you campaign',
  `start_date` datetime NOT NULL COMMENT 'the date and time to send the campaign',
  `user_id` int(11) NOT NULL COMMENT 'the id of the user that created the campaign',
  `status` tinyint(4) NOT NULL,
  `end_date` datetime NOT NULL,
  `created_date` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dumping structure for table interware.campaign_mail
CREATE TABLE IF NOT EXISTS `campaign_mail` (
  `campaign_id` int(11) NOT NULL COMMENT 'foreign key from campaign table',
  `mail_id` int(11) NOT NULL COMMENT 'foreign key from mail table',
  `send_start_date` datetime default NULL,
  `send_end_date` datetime default NULL,
  `status` tinyint(4) default NULL,
  KEY `campaign_id` (`campaign_id`,`mail_id`),
  KEY `send_start_date` (`send_start_date`),
  KEY `campaign_mail_mail_id` (`mail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dumping structure for table interware.campaign_sms
CREATE TABLE IF NOT EXISTS `campaign_sms` (
  `campaign_id` int(10) NOT NULL,
  `sms_id` int(10) NOT NULL,
  `send_start_date` datetime default NULL,
  `send_end_date` datetime default NULL,
  `status` tinyint(4) default NULL,
  UNIQUE KEY `campaign_id` (`campaign_id`,`sms_id`),
  KEY `status` (`status`),
  KEY `send_start_date` (`send_start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dumping structure for table interware.campaign_social
CREATE TABLE IF NOT EXISTS `campaign_social` (
  `id` int(10) NOT NULL auto_increment,
  `social_post_id` int(10) NOT NULL,
  `social_network_oauth_id` int(10) NOT NULL,
  `send_date` datetime NOT NULL,
  `sent` int(10) NOT NULL default '0',
  `campaign_id` int(10) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `social_post_id_social_network_oauth_id` (`social_post_id`,`social_network_oauth_id`),
  KEY `send_date` (`send_date`),
  KEY `FK__social_network_oauth` (`social_network_oauth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dumping structure for table interware.event_log
CREATE TABLE IF NOT EXISTS `event_log` (
  `id` int(10) NOT NULL auto_increment,
  `user_id` int(10) NOT NULL,
  `description` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dumping structure for table interware.list
CREATE TABLE IF NOT EXISTS `list` (
  `id` int(11) NOT NULL auto_increment,
  `mail_id` int(11) default NULL,
  `sms_id` int(11) default NULL,
  `email` varchar(255) default NULL COMMENT 'recipient email address',
  `msisdn` varchar(255) default NULL COMMENT 'recipient email address',
  `field1` varchar(255) default NULL COMMENT 'personalization field',
  `field2` varchar(255) default NULL COMMENT 'personalization field',
  `field3` varchar(255) default NULL COMMENT 'personalization field',
  `field4` varchar(255) default NULL COMMENT 'personalization field',
  `status` tinyint(4) NOT NULL default '0' COMMENT '-1=error; 0=not sent; 1=queued; 2=send',
  `date_queued` datetime default NULL COMMENT 'date when email was queued to recipient',
  PRIMARY KEY  (`id`),
  KEY `mail_id` (`mail_id`),
  KEY `sent` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dumping structure for table interware.mail
CREATE TABLE IF NOT EXISTS `mail` (
  `id` int(11) NOT NULL auto_increment COMMENT 'unique mail identifier',
  `from_address` varchar(255) NOT NULL COMMENT 'The source address of the email',
  `bcc_address` varchar(255) default NULL COMMENT 'anyone you wish to cc',
  `subject` tinytext NOT NULL COMMENT 'Email subject',
  `message` longtext NOT NULL COMMENT 'email content',
  `status` tinyint(4) NOT NULL default '0' COMMENT '0=not sent;1=queued;2=sent',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Contains email send details';

# Dumping structure for table interware.mail_attachment
CREATE TABLE IF NOT EXISTS `mail_attachment` (
  `id` int(10) NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL,
  `size` int(10) NOT NULL,
  `data` blob NOT NULL,
  `mail_id` int(11) NOT NULL,
  `mime_type` varchar(50) default NULL,
  PRIMARY KEY  (`id`),
  KEY `mail_id` (`mail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dumping structure for table interware.mail_sent
CREATE TABLE IF NOT EXISTS `mail_sent` (
  `mail_id` int(11) NOT NULL COMMENT 'foreign key from the mail table',
  `list_id` int(11) NOT NULL COMMENT 'foreign key from the list table',
  `message` longtext NOT NULL COMMENT 'the actual message sent to the user',
  `date_sent` datetime NOT NULL COMMENT 'the date and time the email was sent',
  KEY `mail_id` (`mail_id`,`list_id`),
  KEY `mail_sent_list_id` (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dumping structure for table interware.session
CREATE TABLE IF NOT EXISTS `session` (
  `encrypted_session_string` varchar(256) NOT NULL,
  `session_string` varchar(50) NOT NULL,
  `user_id` int(10) NOT NULL,
  `expiry_date` datetime NOT NULL,
  `start_date` datetime NOT NULL,
  `last_ip` varchar(16) NOT NULL,
  PRIMARY KEY  (`encrypted_session_string`),
  KEY `user_id` (`user_id`),
  KEY `expiry_date` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dumping structure for table interware.sms
CREATE TABLE IF NOT EXISTS `sms` (
  `id` int(10) NOT NULL auto_increment,
  `text` varchar(160) NOT NULL,
  `created_date` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `created_date` (`created_date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dumping structure for table interware.sms_sent
CREATE TABLE IF NOT EXISTS `sms_sent` (
  `sms_id` int(10) NOT NULL,
  `list_id` int(10) NOT NULL,
  `message` varchar(160) NOT NULL,
  `date_sent` datetime NOT NULL,
  KEY `sms_id` (`sms_id`),
  KEY `list_id` (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dumping structure for table interware.social_network_oauth
CREATE TABLE IF NOT EXISTS `social_network_oauth` (
  `id` int(10) NOT NULL auto_increment COMMENT 'unique identifier',
  `user_id` int(10) NOT NULL default '0' COMMENT 'user_id of the user that added the account',
  `access_token` varchar(255) NOT NULL COMMENT 'token',
  `access_token_secret` varchar(255) NOT NULL COMMENT 'token secret',
  `description` varchar(255) NOT NULL COMMENT 'description of network',
  `network` varchar(50) NOT NULL COMMENT 'twitter or facebook',
  `extra` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `description` (`description`),
  UNIQUE KEY `description_network_extra` (`description`,`network`,`extra`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Social network login credentials';

# Dumping structure for table interware.social_post
CREATE TABLE IF NOT EXISTS `social_post` (
  `id` int(10) NOT NULL auto_increment COMMENT 'unique identifier for post',
  `user_id` int(10) NOT NULL COMMENT 'the user that created the post',
  `text` varchar(255) NOT NULL COMMENT 'the text to be posted to the social networks',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dumping structure for table interware.unsubscribe
CREATE TABLE IF NOT EXISTS `unsubscribe` (
  `email_address` varchar(50) NOT NULL,
  PRIMARY KEY  (`email_address`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='List of people who have opted out of receiving communication';

# Dumping structure for table interware.user
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL COMMENT '3=admin; 2=moderator; 1=viewer; 0=guest',
  `permission_emails` tinyint(4) NOT NULL default '0',
  `permission_social_medias` tinyint(4) NOT NULL default '0',
  `permission_reports` tinyint(4) NOT NULL default '0',
  `permission_users` tinyint(4) NOT NULL default '0',
  `permission_campaigns` tinyint(4) NOT NULL default '0',
  `permission_sms` tinyint(4) NOT NULL default '0',
  `permission_recipients` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;