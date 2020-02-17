DROP TABLE IF EXISTS wcf1_discord_server;
CREATE TABLE wcf1_discord_server (
    serverID INT(10) NOT NULL AUTO_INCREMENT,
    botID INT(10) NOT NULL,
    guildID BIGINT(20) NOT NULL,
    guildName VARCHAR(100),
    guildIcon VARCHAR(50),
    webhookName VARCHAR(50) NOT NULL,
    serverTime INT(10) NOT NULL,
    PRIMARY KEY (serverID)
);
ALTER TABLE wcf1_discord_bot DROP COLUMN guildID;
ALTER TABLE wcf1_discord_bot DROP COLUMN guildName;
ALTER TABLE wcf1_discord_bot DROP COLUMN guildIcon;
ALTER TABLE wcf1_discord_bot DROP COLUMN webhookName;

ALTER TABLE wcf1_discord_server ADD FOREIGN KEY (botID) REFERENCES wcf1_discord_bot (botID) ON DELETE CASCADE;
