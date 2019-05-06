ALTER TABLE wcf1_category ADD discordChannelIDs TEXT;
ALTER TABLE wcf1_category ADD discordPostPrefix VARCHAR(30);
ALTER TABLE wcf1_category ADD discordPostTitleInContext TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_category ADD discordPostType TINYINT(1) NOT NULL DEFAULT 0;

DROP TABLE IF EXISTS wcf1_discord_bot;
CREATE TABLE wcf1_discord_bot (
    botID INT(10) NOT NULL AUTO_INCREMENT,
    botName VARCHAR(50) NOT NULL,
    botToken VARCHAR(100) NOT NULL,
    guildID BIGINT(20) NOT NULL,
    guildName VARCHAR(100),
    guildIcon VARCHAR(50),
    webhookName VARCHAR(50) NOT NULL,
    clientID BIGINT(20),
    clientSecret VARCHAR(100),
    botTime INT(10) NOT NULL,
    PRIMARY KEY (botID)
);

DROP TABLE IF EXISTS wcf1_discord_webhook;
CREATE TABLE wcf1_discord_webhook (
    webhookID BIGINT(20) NOT NULL,
    channelID BIGINT(10) NOT NULL,
    botID INT(10) NOT NULL,
    webhookToken VARCHAR(100) NOT NULL,
    webhookName VARCHAR(50) NOT NULL,
    webhookTitle VARCHAR(100) NOT NULL,
    usageBy VARCHAR(100) NOT NULL,
    webhookTime INT(10) NOT NULL,
    PRIMARY KEY (webhookID)
);
ALTER TABLE wcf1_discord_webhook ADD FOREIGN KEY (botID) REFERENCES wcf1_discord_bot (botID) ON DELETE CASCADE;
