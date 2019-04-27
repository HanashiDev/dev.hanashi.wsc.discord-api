-- TODO: nächtlicher Cronjob um aktuelle Guild-Informationen für Bot abzurufen
CREATE TABLE wcf1_discord_bot (
    botID INT(10) NOT NULL AUTO_INCREMENT,
    botName VARCHAR(50) NOT NULL,
    guildID BIGINT(20) NOT NULL,
    guildName VARCHAR(100) NOT NULL,
    guildIcon VARCHAR(50),
    clientID BIGINT(20) NOT NULL,
    clientSecret VARCHAR(50) NOT NULL,
    botToken VARCHAR(50) NOT NULL,
    displayName VARCHAR(50) NOT NULL,
    PRIMARY KEY (botID)
);
