CREATE TABLE `users` (
`userID`  int NOT NULL AUTO_INCREMENT,
  `userFullName` varchar(500) NOT NULL,
  `username` varchar(45) NOT NULL,
  `password` varchar(45) NOT NULL,
  `status` enum('User', 'Admin', 'Banned') NOT NULL DEFAULT 'User',
  PRIMARY KEY  (`userID`),
  KEY `idx_users_userID` (`userID`),
  UNIQUE KEY `unq_idx_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `mailbox` (
  `messageID` int(11) NOT NULL auto_increment,
  `subject` blob default NULL,
  `msgTime` datetime default NULL,
  `msgText` blob default NULL,
  `sender_userID` int NOT NULL,
  `receiver_userID` int NOT NULL,
  `status` enum('New', 'Read', 'Deleted') default 'New',
  PRIMARY KEY  (`messageID`),
  KEY `idx_mailbox_messageID` (`messageID`),
  KEY `idx_sender_userID` (`sender_userID`),
  KEY `idx_receiver_userID` (`receiver_userID`),
  CONSTRAINT `FK_receiver_userID` FOREIGN KEY (`receiver_userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_sender_userID` FOREIGN KEY (`sender_userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;

CREATE TABLE `forum` (
  `forumID` int NOT NULL auto_increment,
  `forumName` varchar(100) NOT NULL,
  `description` text default NULL,
  `status` enum('Review', 'Live') NOT NULL default 'Review',
  `picture` longblob default NULL,
  `moderator_userID` int NOT NULL,
  PRIMARY KEY  (`forumID`),
  KEY `idx_moderator_userID` (`moderator_userID`),
  CONSTRAINT `FK_moderator_userID` FOREIGN KEY (`moderator_userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `thread` (
  `forumID` int NOT NULL,
  `threadID` int NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `addedDate` datetime NOT NULL,
  `status` enum('Open', 'Closed', 'Removed') NOT NULL DEFAULT 'Open',
  `start_userID` int NOT NULL,
  PRIMARY KEY (`forumID`,`threadID`),
  KEY `idx_threadID` (`threadID`),
  CONSTRAINT `FK_start_userID` FOREIGN KEY (`start_userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ban` (
  `userID` int NOT NULL,
  `forumID` int NOT NULL,
  `expiration` datetime NOT NULL,
  PRIMARY KEY (`userID`,`forumID`),
  CONSTRAINT `FK_ban_userID` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_ban_forumID` FOREIGN KEY (`forumID`) REFERENCES `forum` (`forumID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `post` (
  `forumID` int NOT NULL,
  `threadID` int NOT NULL,
  `postID` int NOT NULL AUTO_INCREMENT,
  `addedDate` datetime NOT NULL,
  `text` TEXT,
  `poster_userID` int NOT NULL,
  `isFirst` bit NOT NULL DEFAULT 0,
  PRIMARY KEY (`forumID`,`threadID`,`postID`),
  KEY `idx_postID` (`postID`),
  KEY `idx_threadID` (`threadID`),
  CONSTRAINT `FK_post_userID` FOREIGN KEY (`poster_userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
