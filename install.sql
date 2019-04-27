-- TODO: nächtlicher Cronjob um aktuelle Guild-Informationen für Bot abzurufen
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

CREATE TABLE wcf1_discord_webhook (
    channelID INT(10) NOT NULL,
    botID INT(10) NOT NULL,
    webhookID BIGINT(20) NOT NULL,
    webhookToken VARCHAR(100) NOT NULL,
    webhookName VARCHAR(50) NOT NULL,
    PRIMARY KEY (channelID)
);
ALTER TABLE wcf1_discord_webhook ADD FOREIGN KEY (botID) REFERENCES wcf1_discord_bot (botID) ON DELETE CASCADE;
