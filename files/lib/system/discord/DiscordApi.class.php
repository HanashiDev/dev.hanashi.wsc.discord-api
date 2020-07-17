<?php
namespace wcf\system\discord;
use wcf\data\discord\bot\DiscordBot;
use wcf\system\exception\HTTPNotFoundException;
use wcf\system\exception\HTTPServerErrorException;
use wcf\system\exception\HTTPUnauthorizedException;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\exception\HTTPException;
use wcf\util\HTTPRequest;
use wcf\util\JSON;

/**
 * Klasse zum Handlen der Discord-API-Aufrufe
 *
 * @author	Peter Lohse <hanashi@hanashi.eu>
 * @copyright	Hanashi
 * @license	Freie Lizenz (https://hanashi.eu/freie-lizenz/)
 * @package	WoltLabSuite\Core\System\Discord
 */
class DiscordApi {
    /**
     * URL zur Discord-API
     * 
     * @var string
     */
    protected $apiUrl = 'https://discord.com/api';

    /**
     * Server-ID des Discord-Servers
     * 
     * @var integer
     */
    protected $guildID;

    /**
     * Geheimer Schlüssel des Discord-Bots
     * 
     * @var string
     */
    protected $botToken;

    /**
     * Bot-Typ
     * Bot = Bot
     * Bearer = Benutzer
     * 
     * @var string
     */
    protected $botType;

    /**
     * Instanz des Discord-Bot-Objekts
     * 
     * @var DiscordBot
     */
    protected $discordBot;

    /**
     * Konstruktor
     * 
     * @param   integer $guildID        Server-ID des Discord-Servers
     * @param   string  $botToken       Geheimer Schlüssel des Discord-Bots
     * @param   string  $botType        Bot-Typ
     */
    public function __construct($guildID, $botToken, $botType = 'Bot') {
        $this->guildID = $guildID;
        $this->botToken = $botToken;
        $this->botType = $botType;
    }

    /**
     * Erstellt ein API-Objekt anhand der Bot-ID
     * 
     * @param   integer $botID  ID des Bots
     * @return  DiscordApi
     */
    public static function getApiByID($botID) {
        $discordBot = new DiscordBot($botID);
        if (!$discordBot->botID) return null;

        $discordApi = $discordBot->getDiscordApi();
        $discordApi->discordBot($discordBot);
        return $discordApi;
    }

    public function discordBot($bot) {
        $this->discordBot = $bot;
    }

    /////////////////////////////////////
    // Audit Log Start
    /////////////////////////////////////

    /**
     * Returns an audit log object for the guild.
     * Requires the 'VIEW_AUDIT_LOG' permission.
     * 
     * @return array
     */
    public function getGuildAuditLog($params = []) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/audit-logs';
        if (!empty($params)) {
            $url .= '?'.http_build_query($params, '', '&');
        }
        return $this->execute($url);
    }

    /////////////////////////////////////
    // Audit Log End
    /////////////////////////////////////

    /////////////////////////////////////
    // Channels Start
    /////////////////////////////////////

    /**
     * Get a channel by ID. Returns a channel object.
     * 
     * @param   integer $channelID  Channel-ID
     * @return  array
     */
    public function getChannel($channelID) {
        $url = $this->apiUrl . '/channels/' . $channelID;
        return $this->execute($url);
    }

    /**
     * Update a channels settings.
     * Requires the MANAGE_CHANNELS permission for the guild.
     * Returns a channel on success, and a 400 BAD REQUEST on invalid parameters.
     * Fires a Channel Update Gateway event.
     * If modifying a category, individual Channel Update events will fire for each child channel that also changes.
     * For the PATCH method, all the JSON Params are optional.
     * 
     * @param   integer $channelID  Channel-ID
     * @param   array   $params     JSON-Parameter
     * @return  array
     */
    public function modifyChannel($channelID, $params) {
        $url = $this->apiUrl . '/channels/' . $channelID;
        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Delete a channel, or close a private message.
     * Requires the MANAGE_CHANNELS permission for the guild.
     * Deleting a category does not delete its child channels; they will have their parent_id removed and a Channel Update Gateway event will fire for each of them.
     * Returns a channel object on success.
     * Fires a Channel Delete Gateway event.
     * 
     * @param   integer $channelID  Channel-ID
     * @return  array
     */
    public function deleteChannel($channelID) {
        $url = $this->apiUrl . '/channels/' . $channelID;
        return $this->execute($url, 'DELETE');
    }

    /**
     * alias for deleteChannel
     * @see self::deleteChannel()
     */
    public function closeChannel($channelID) {
        return $this->deleteChannel($channelID);
    }

    /**
     * Returns the messages for a channel.
     * If operating on a guild channel, this endpoint requires the VIEW_CHANNEL permission to be present on the current user.
     * If the current user is missing the 'READ_MESSAGE_HISTORY' permission in the channel then this will return no messages (since they cannot read the message history).
     * Returns an array of message objects on success.
     * 
     * @param   integer $channelID  Channel-ID
     * @param   array   $params     HTTP-Parameter
     * @return  array
     */
    public function getChannelMessages($channelID, $params = []) {
        $url = $this->apiUrl . '/channels/' . $channelID . '/messages';
        if (!empty($params)) {
            $url .= '?'.http_build_query($params, '', '&');
        }
        return $this->execute($url);
    }

    /**
     * Returns a specific message in the channel.
     * If operating on a guild channel, this endpoint requires the 'READ_MESSAGE_HISTORY' permission to be present on the current user. Returns a message object on success.
     * 
     * @param   integer $channelID  Channel-ID
     * @param   integer $messageID  ID der Nachricht
     * 
     * @return  array
     */
    public function getChannelMessage($channelID, $messageID) {
        $url = $this->apiUrl . '/channels/' . $channelID . '/messages/' . $messageID;
        return $this->execute($url);
    }

    /**
     * Post a message to a guild text or DM channel.
     * If operating on a guild channel, this endpoint requires the SEND_MESSAGES permission to be present on the current user.
     * If the tts field is set to true, the SEND_TTS_MESSAGES permission is required for the message to be spoken.
     * Returns a message object.
     * Fires a Message Create Gateway event.
     * See message formatting for more information on how to properly format messages.
     * The maximum request size when sending a message is 8MB.
     * 
     * @param   integer $channelID  Channel-ID
     * @param   array   $params     POST-Parameter
     * @return  array
     */
    public function createMessage($channelID, $params) {
        $url = $this->apiUrl . '/channels/' . $channelID . '/messages';
        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Create a reaction for the message.
     * emoji takes the form of name:id for custom guild emoji, or Unicode characters.
     * This endpoint requires the 'READ_MESSAGE_HISTORY' permission to be present on the current user.
     * Additionally, if nobody else has reacted to the message using this emoji, this endpoint requires the 'ADD_REACTIONS' permission to be present on the current user.
     * Returns a 204 empty response on success.
     * 
     * @param   integer $channelID  Channel-ID
     * @param   integer $messageID  ID der Nachricht
     * @param   integer $emoji      ID des Emoji oder Unicode des Emoji
     * @return  array
     */
    public function createReaction($channelID, $messageID, $emoji) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/messages/'.$messageID.'/reactions/'.$emoji.'/@me';
        return $this->execute($url, 'PUT');
    }

    /**
     * Delete a reaction the current user has made for the message.
     * Returns a 204 empty response on success.
     * 
     * @param   integer $channelID  Channel-ID
     * @param   integer $messageID  ID der Nachricht
     * @param   integer $emoji      ID des Emoji oder Unicode des Emoji
     * @return  array
     */
    public function deleteOwnReaction($channelID, $messageID, $emoji) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/messages/'.$messageID.'/reactions/'.$emoji.'/@me';
        return $this->execute($url, 'DELETE');
    }

    /**
     * Deletes another user's reaction.
     * This endpoint requires the 'MANAGE_MESSAGES' permission to be present on the current user.
     * Returns a 204 empty response on success.
     * 
     * @param   integer $channelID  Channel-ID
     * @param   integer $messageID  ID der Nachricht
     * @param   integer $emoji      ID des Emoji oder Unicode des Emoji
     * @param   integer $userID     ID des Benutzers
     * @return  array
     */
    public function deleteUserReaction($channelID, $messageID, $emoji, $userID) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/messages/'.$messageID.'/reactions/'.$emoji.'/' . $userID;
        return $this->execute($url, 'DELETE');
    }

    /**
     * Get a list of users that reacted with this emoji.
     * Returns an array of user objects on success.
     * 
     * @param   integer $channelID  Channel-ID
     * @param   integer $messageID  ID der Nachricht
     * @param   integer $emoji      ID des Emoji oder Unicode des Emoji
     * @param   integer $params     optionale Query-Parameters
     * @return  array
     */
    public function getReactions($channelID, $messageID, $emoji, $params = []) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/messages/'.$messageID.'/reactions/'.$emoji;
        if (!empty($params)) {
            $url .= '?'.http_build_query($params, '', '&');
        }
        return $this->execute($url);
    }

    /**
     * Deletes all reactions on a message.
     * This endpoint requires the 'MANAGE_MESSAGES' permission to be present on the current user.
     * 
     * @param   integer $channelID  Channel-ID
     * @param   integer $messageID  ID der Nachricht
     * @return  array
     */
    public function deleteAllReactions($channelID, $messageID) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/messages/'.$messageID.'/reactions';
        return $this->execute($url, 'DELETE');
    }

    /**
     * Edit a previously sent message.
     * You can only edit messages that have been sent by the current user.
     * Returns a message object.
     * Fires a Message Update Gateway event.
     * 
     * @param   integer $channelID  Channel-ID
     * @param   integer $messageID  ID der Nachricht
     * @param   integer $params     optionale Query-Parameters
     * @return  array
     */
    public function editMessage($channelID, $messageID, $params) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/messages/'.$messageID;
        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Delete a message.
     * If operating on a guild channel and trying to delete a message that was not sent by the current user, this endpoint requires the MANAGE_MESSAGES permission.
     * Returns a 204 empty response on success.
     * Fires a Message Delete Gateway event.
     * 
     * @param   integer $channelID  Channel-ID
     * @param   integer $messageID  ID der Nachricht
     * @return  array
     */
    public function deleteMessage($channelID, $messageID) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/messages/'.$messageID;
        return $this->execute($url, 'DELETE');
    }

    /**
     * Delete multiple messages in a single request.
     * This endpoint can only be used on guild channels and requires the MANAGE_MESSAGES permission.
     * Returns a 204 empty response on success.
     * Fires multiple Message Delete Gateway events.
     * Any message IDs given that do not exist or are invalid will count towards the minimum and maximum message count (currently 2 and 100 respectively).
     * Additionally, duplicated IDs will only be counted once.
     * This endpoint will not delete messages older than 2 weeks, and will fail if any message provided is older than that.
     * 
     * @param   integer $channelID  Channel-ID
     * @param   array   $messageIDs IDs von Nachrichten
     * @return  array
     */
    public function bulkDeleteMessage($channelID, $messageIDs) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/messages/bulk-delete';
        return $this->execute($url, 'POST', ['messages' => $messageIDs], 'application/json');
    }

    /**
     * Edit the channel permission overwrites for a user or role in a channel.
     * Only usable for guild channels.
     * Requires the MANAGE_ROLES permission.
     * Returns a 204 empty response on success.
     * For more information about permissions, see permissions.
     * 
     * @param   integer $channelID      Channel-ID
     * @param   integer $overwriteID    Overwrite-ID
     * @param   array   $params         Parameter
     * @return  array
     */
    public function editChannelPermissions($channelID, $overwriteID, $params) {
        $url = $this->apiURL . '/channels/'.$channelID.'/permissions/'.$overwriteID;
        return $this->execute($url, 'PUT', $params, 'application/json');
    }

    /**
     * Returns a list of invite objects (with invite metadata) for the channel.
     * Only usable for guild channels.
     * Requires the MANAGE_CHANNELS permission.
     * 
     * @param   integer $channelID  Channel-ID
     * @return  array
     */
    public function getChannelInvites($channelID) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/invites';
        return $this->execute($url);
    }

    /**
     * Create a new invite object for the channel.
     * Only usable for guild channels.
     * Requires the CREATE_INSTANT_INVITE permission.
     * All JSON parameters for this route are optional, however the request body is not.
     * If you are not sending any fields, you still have to send an empty JSON object ({}).
     * Returns an invite object.
     * 
     * @param   integer $channelID  Channel-ID
     * @param   array   $params     optionale Parameter
     * @return  array
     */
    public function createChannelInvite($channelID, $params = []) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/invites';
        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Delete a channel permission overwrite for a user or role in a channel.
     * Only usable for guild channels.
     * Requires the MANAGE_ROLES permission.
     * Returns a 204 empty response on success.
     * For more information about permissions, see permissions
     * 
     * @param   integer $channelID      Channel-ID
     * @param   integer $overwriteID    Overwrite-ID
     * @return  array
     */
    public function deleteChannelPermission($channelID, $overwriteID) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/permissions/'.$overwriteID;
        return $this->execute($url, 'DELETE');
    }

    /**
     * Post a typing indicator for the specified channel.
     * Generally bots should not implement this route.
     * However, if a bot is responding to a command and expects the computation to take a few seconds, this endpoint may be called to let the user know that the bot is processing their message.
     * Returns a 204 empty response on success.
     * Fires a Typing Start Gateway event.
     * 
     * @param   integer $channelID      Channel-ID
     * @return  array
     */
    public function triggerTypingIndicator($channelID) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/typing';
        return $this->execute($url, 'POST');
    }

    /**
     * Returns all pinned messages in the channel as an array of message objects.
     * 
     * @param   integer $channelID      Channel-ID
     * @return  array
     */
    public function getPinnedMessages($channelID) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/pins';
        return $this->execute($url);
    }

    /**
     * Pin a message in a channel.
     * Requires the MANAGE_MESSAGES permission.
     * Returns a 204 empty response on success.
     * 
     * @param   integer $channelID      Channel-ID
     * @param   integer $messageID  ID der Nachricht
     * @return  array
     */
    public function addPinnedChannelMessage($channelID, $messageID) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/pins/'.$messageID;
        return $this->execute($url, 'PUT');
    }

    /**
     * Delete a pinned message in a channel.
     * Requires the MANAGE_MESSAGES permission.
     * Returns a 204 empty response on success.
     * 
     * @param   integer $channelID      Channel-ID
     * @param   integer $messageID  ID der Nachricht
     * @return  array
     */
    public function deletePinnedChannelMessage($channelID, $messageID) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/pins/'.$messageID;
        return $this->execute($url, 'DELETE');
    }

    /**
     * Adds a recipient to a Group DM using their access token
     * 
     * @param   integer $channelID  Channel-ID
     * @param   integer $userID     User that should join
     * @param   array   $params     optionale Parameter
     * @return  array
     */
    public function groupDMAddRecipient($channelID, $userID, $params = []) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/recipients/'.$userID;
        return $this->execute($url, 'PUT', $params, 'application/json');
    }

    /**
     * Removes a recipient from a Group DM
     * 
     * @param   integer $channelID  Channel-ID
     * @param   integer $userID     User that should join
     * @return  array
     */
    public function groupDMRemoveRecipient($channelID, $userID) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/recipients/'.$userID;
        return $this->execute($url, 'DELETE');
    }

    /////////////////////////////////////
    // Channels End
    /////////////////////////////////////

    /////////////////////////////////////
    // Emoji Start
    /////////////////////////////////////

    /**
     * Returns a list of emoji objects for the given guild.
     * 
     * @return  array
     */
    public function listGuildEmojis() {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/emojis';
        return $this->execute($url);
    }

    /**
     * Returns an emoji object for the given guild and emoji IDs
     * 
     * @param   integer $emojiID    ID des Emojis
     * @return array
     */
    public function getGuildEmoji($emojiID) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/emojis/'.$emojiID;
        return $this->execute($url);
    }

    /**
     * Create a new emoji for the guild.
     * Requires the MANAGE_EMOJIS permission.
     * Returns the new emoji object on success.
     * Fires a Guild Emojis Update Gateway event.
     * Emojis and animated emojis have a maximum file size of 256kb.
     * Attempting to upload an emoji larger than this limit will fail and return 400 Bad Request and an error message, but not a JSON status code.
     * 
     * @param   string  $name   Name des Emojis
     * @param   string  $image  Bild als base64 code
     * @param   array   $roles  Gruppen die diesen Emoji nutzen dürfen
     * @return  array
     */
    public function createGuildEmoji($name, $image, $roles = []) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/emojis';
        $params = [
            'name' => $name,
            'image' => $image
        ];
        if (count($roles) > 0) {
            $params['roles'] = $roles;
        }
        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Modify the given emoji.
     * Requires the MANAGE_EMOJIS permission.
     * Returns the updated emoji object on success.
     * Fires a Guild Emojis Update Gateway event.
     * 
     * @param   integer $emojiID    ID des Emojis
     * @param   array   $params     Parameter
     * @return  array
     */
    public function modifyGuildEmoji($emojiID, array $params) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/emojis/'.$emojiID;
        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Delete the given emoji.
     * Requires the MANAGE_EMOJIS permission.
     * Returns 204 No Content on success.
     * Fires a Guild Emojis Update Gateway event.
     * 
     * @param   integer $emojiID    ID des Emojis
     * @return  array
     */
    public function deleteGuildEmoji($emojiID) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/emojis/'.$emojiID;
        return $this->execute($url, 'DELETE');
    }

    /////////////////////////////////////
    // Emoji End
    /////////////////////////////////////

    /////////////////////////////////////
    // Guild Start
    /////////////////////////////////////

    /**
     * Create a new guild. Returns a guild object on success. Fires a Guild Create Gateway event.
     * 
     * @param   array   $params     Paramter
     * @return  array
     */
    public function createGuild($params) {
        $url = $this->apiUrl . '/guilds';
        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Returns the guild object for the given id.
     * 
     * @return  array
     */
    public function getGuild() {
        $url = $this->apiUrl . '/guilds/'.$this->guildID;
        return $this->execute($url);
    }

    /**
     * Modify a guild's settings.
     * Requires the MANAGE_GUILD permission.
     * Returns the updated guild object on success.
     * Fires a Guild Update Gateway event.
     * 
     * @param   array   $params     Parameter
     * @return  array
     */
    public function modifyGuild($params) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID;
        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Delete a guild permanently.
     * User must be owner.
     * Returns 204 No Content on success.
     * Fires a Guild Delete Gateway event.
     * 
     * @return  array
     */
    public function deleteGuild() {
        $url = $this->apiUrl . '/guilds/'.$this->guildID;
        return $this->execute($url, 'DELETE');
    }

    /**
     * Returns a list of guild channel objects.
     * 
     * @return  array
     */
    public function getGuildChannels() {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/channels';
        return $this->execute($url);
    }

    /**
     * Create a new channel object for the guild.
     * Requires the MANAGE_CHANNELS permission.
     * Returns the new channel object on success.
     * Fires a Channel Create Gateway event.
     * 
     * @param   array   $params     JSON-Parameter
     * @return  array
     */
    public function createGuildChannel($params) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/channels';
        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Modify the positions of a set of channel objects for the guild.
     * Requires MANAGE_CHANNELS permission.
     * Returns a 204 empty response on success.
     * Fires multiple Channel Update Gateway events.
     * Only channels to be modified are required, with the minimum being a swap between at least two channels.
     * 
     * @param   integer     $channelID  ID des Channels
     * @param   integer     $position   Position wo der Channel landen soll
     * @return  array
     */
    public function modifyGuildChannelPositions($channelID, $position) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/channels';
        $params = [
            'id' => $channelID,
            'position' => $position
        ];
        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Returns a guild member object for the specified user.
     * 
     * @param   integer     $userID     ID des Benutzer
     * @return  array
     */
    public function getGuildMember($userID) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/members/'.$userID;
        return $this->execute($url);
    }

    /**
     * Returns a list of guild member objects that are members of the guild.
     * 
     * @param   array   $params     Parameter
     * @return  array
     */
    public function listGuildMembers($params = []) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/members';
        if (!empty($params)) {
            $url .= '?'.http_build_query($params, '', '&');
        }
        return $this->execute($url);
    }

    /**
     * Adds a user to the guild, provided you have a valid oauth2 access token for the user with the guilds.join scope.
     * Returns a 201 Created with the guild member as the body, or 204 No Content if the user is already a member of the guild.
     * Fires a Guild Member Add Gateway event.
     * Requires the bot to have the CREATE_INSTANT_INVITE permission.
     * All parameters to this endpoint except for access_token are optional.
     * 
     * @param   integer     $userID         ID des Benutzers
     * @param   string      $accessToken    Access-Token des Benutzer
     * @param   array       $params         Zusätzliche optionale Parameter
     * @return  array
     */
    public function addGuildMember($userID, $accessToken, $params = []) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/members/'.$userID;
        $params = array_merge([
            'access_token' => $accessToken
        ], $params);
        return $this->execute($url, 'PUT', $params, 'application/json');
    }

    /**
     * Modify attributes of a guild member.
     * Returns a 204 empty response on success.
     * Fires a Guild Member Update Gateway event.
     * All parameters to this endpoint are optional.
     * When moving members to channels, the API user must have permissions to both connect to the channel and have the MOVE_MEMBERS permission.
     * 
     * @param   integer $userID     ID des Benutzers
     * @param   array   $params     JSON-Parameter
     * @return  array
     */
    public function modifyGuildMember($userID, $params) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/members/'.$userID;
        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Modifies the nickname of the current user in a guild.
     * Returns a 200 with the nickname on success.
     * Fires a Guild Member Update Gateway event.
     * 
     * @param   string  $nick   Neuer Nickname
     * @return  array
     */
    public function modifyCurrentUserNick($nick) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/members/@me/nick';
        $params = [
            'nick' => $nick
        ];
        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Adds a role to a guild member.
     * Requires the MANAGE_ROLES permission.
     * Returns a 204 empty response on success.
     * Fires a Guild Member Update Gateway event.
     * 
     * @param   integer     $userID     ID des Benutzer
     * @param   integer     $roleID     ID der Benutzergruppe
     * @return  array
     */
    public function addGuildMemberRole($userID, $roleID) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/members/'.$userID.'/roles/'.$roleID;
        return $this->execute($url, 'PUT');
    }

    /**
     * Removes a role from a guild member.
     * Requires the MANAGE_ROLES permission.
     * Returns a 204 empty response on success.
     * Fires a Guild Member Update Gateway event.
     * 
     * @param   integer     $userID     ID des Benutzer
     * @param   integer     $roleID     ID der Benutzergruppe
     * @return  array
     */
    public function removeGuildMemberRole($userID, $roleID) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/members/'.$userID.'/roles/'.$roleID;
        return $this->execute($url, 'DELETE');
    }

    /**
     * Remove a member from a guild.
     * Requires KICK_MEMBERS permission.
     * Returns a 204 empty response on success.
     * Fires a Guild Member Remove Gateway event.
     * 
     * @param   integer     $userID     ID des Benutzer
     * @return  array
     */
    public function removeGuildMember($userID) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/members/'.$userID;
        return $this->execute($url, 'DELETE');
    }

    /**
     * Returns a list of ban objects for the users banned from this guild.
     * Requires the BAN_MEMBERS permission.
     * 
     * @return array
     */
    public function getGuildBans() {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/bans';
        return $this->execute($url);
    }

    /**
     * Returns a ban object for the given user or a 404 not found if the ban cannot be found.
     * Requires the BAN_MEMBERS permission.
     * 
     * @param   integer     $userID     ID des Benutzer
     * @return  array
     */
    public function getGuildBan($userID) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/bans/'.$userID;
        return $this->execute($url);
    }

    /**
     * Create a guild ban, and optionally delete previous messages sent by the banned user.
     * Requires the BAN_MEMBERS permission.
     * Returns a 204 empty response on success.
     * Fires a Guild Ban Add Gateway event.
     * 
     * @param   integer     $userID     ID des Benutzer
     * @param   array       $params     optionale Parameter
     * @return  array
     */
    public function createGuildBan($userID, $params = []) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/bans/'.$userID;
        return $this->execute($url, 'PUT', $params, 'application/json');
    }

    /**
     * Remove the ban for a user. Requires the BAN_MEMBERS permissions. Returns a 204 empty response on success. Fires a Guild Ban Remove Gateway event.
     * 
     * @param   integer     $userID     ID des Benutzer
     * @return  array
     */
    public function removeGuildBan($userID) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/bans/'.$userID;
        return $this->execute($url, 'DELETE');
    }

    /**
     * Returns a list of role objects for the guild.
     * 
     * @return array
     */
    public function getGuildRoles() {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/roles';
        return $this->execute($url);
    }

    /**
     * Create a new role for the guild.
     * Requires the MANAGE_ROLES permission.
     * Returns the new role object on success.
     * Fires a Guild Role Create Gateway event.
     * All JSON params are optional.
     * 
     * @param   array   $params     optionale Parameter für das Gruppenrecht
     * @return  array
     */
    public function createGuildRole($params = []) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/roles';
        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Modify the positions of a set of role objects for the guild.
     * Requires the MANAGE_ROLES permission.
     * Returns a list of all of the guild's role objects on success.
     * Fires multiple Guild Role Update Gateway events.
     * 
     * @param   integer     $roleID     ID der Benutzergruppe
     * @param   integer     $position   Position
     * @return  array
     */
    public function modifyGuildRolePosition($roleID, $position) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/roles';
        $params = [
            'id' => $roleID,
            'position' => $position
        ];
        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Modify a guild role.
     * Requires the MANAGE_ROLES permission.
     * Returns the updated role on success.
     * Fires a Guild Role Update Gateway event.
     * 
     * @param   integer     $roleID     ID der Benutzergruppe
     * @param   array       $params     Parameter
     * @return  array
     */
    public function modifyGuildRole($roleID, $params = []) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/roles/'.$roleID;
        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Delete a guild role.
     * Requires the MANAGE_ROLES permission.
     * Returns a 204 empty response on success.
     * Fires a Guild Role Delete Gateway event.
     * 
     * @param   integer     $roleID     ID der Benutzergruppe
     * @return  array
     */
    public function deleteGuildRole($roleID) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/roles/'.$roleID;
        return $this->execute($url, 'DELETE');
    }

    /**
     * Returns an object with one 'pruned' key indicating the number of members that would be removed in a prune operation.
     * Requires the KICK_MEMBERS permission.
     * 
     * @param   integer     $days       number of days to count prune for (1 or more)
     * @return  array
     */
    public function getGuildPruneCount($days = 1) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/prune';
        $url .= '?'.http_build_query([
            'days' => $days
        ], '', '&');
        return $this->execute($url);
    }

    /**
     * Begin a prune operation.
     * Requires the KICK_MEMBERS permission.
     * Returns an object with one 'pruned' key indicating the number of members that were removed in the prune operation.
     * For large guilds it's recommended to set the compute_prune_count option to false, forcing 'pruned' to null.
     * Fires multiple Guild Member Remove Gateway events.
     * 
     * @param   integer     $days               number of days to count prune for (1 or more)
     * @param   boolean     $computePruneCount  whether 'pruned' is returned, discouraged for large guilds
     * @return  array
     */
    public function beginGuildPrune($days, $computePruneCount = false) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/prune';
        $params = [
            'days' => $days,
            'compute_prune_count' => $computePruneCount
        ];
        return $this->execute($url, 'POST', $params);
    }

    /**
     * Returns a list of voice region objects for the guild.
     * Unlike the similar /voice route, this returns VIP servers when the guild is VIP-enabled.
     * 
     * @return  array
     */
    public function getGuildVoiceRegions() {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/regions';
        return $this->execute($url);
    }

    /**
     * Returns a list of invite objects (with invite metadata) for the guild.
     * Requires the MANAGE_GUILD permission.
     * 
     * @return  array
     */
    public function getGuildInvites() {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/invites';
        return $this->execute($url);
    }

    /**
     * Returns a list of integration objects for the guild.
     * Requires the MANAGE_GUILD permission.
     * 
     * @return  array
     */
    public function getGuildIntegrations() {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/integrations';
        return $this->execute($url);
    }

    /**
     * Attach an integration object from the current user to the guild.
     * Requires the MANAGE_GUILD permission.
     * Returns a 204 empty response on success.
     * Fires a Guild Integrations Update Gateway event.
     * 
     * @param   string  $type   the integration type
     * @param   integer $id     the integration id
     * @return  array
     */
    public function createGuildIntegration($type, $id) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/integrations';
        $params = [
            'type' => $type,
            'id' => $id
        ];
        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Modify the behavior and settings of an integration object for the guild.
     * Requires the MANAGE_GUILD permission.
     * Returns a 204 empty response on success.
     * Fires a Guild Integrations Update Gateway event.
     * 
     * @param   integer $integrationID  ID der Integration
     * @param   array   $params         Parameter
     * @return  array
     */
    public function modifyGuildIntegration($integrationID, $params) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/integrations/'.$integrationID;
        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Delete the attached integration object for the guild.
     * Requires the MANAGE_GUILD permission.
     * Returns a 204 empty response on success.
     * Fires a Guild Integrations Update Gateway event.
     * 
     * @param   integer $integrationID  ID der Integration
     * @return  array
     */
    public function deleteIntegration($integrationID) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/integrations/'.$integrationID;
        return $this->execute($url, 'DELETE');
    }

    /**
     * Sync an integration. Requires the MANAGE_GUILD permission.
     * Returns a 204 empty response on success.
     * 
     * @param   integer $integrationID  ID der Integration
     * @return  array
     */
    public function syncGuildIntegration($integrationID) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/integrations/'.$integrationID.'/sync';
        return $this->execute($url, 'POST');
    }

    /**
     * Returns the guild embed object. Requires the MANAGE_GUILD permission.
     * 
     * @return  array
     */
    public function getGuildEmbed() {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/embed';
        return $this->execute($url);
    }

    /**
     * Modify a guild embed object for the guild.
     * All attributes may be passed in with JSON and modified.
     * Requires the MANAGE_GUILD permission.
     * Returns the updated guild embed object.
     * 
     * @param   array   $params Parameter?
     * @return  array
     */
    public function modifyGuildEmbed($params = []) {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/embed';
        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Returns a partial invite object for guilds with that feature enabled.
     * Requires the MANAGE_GUILD permission.
     * code will be null if a vanity url for the guild is not set.
     * 
     * @return array
     */
    public function getGuildVanityUrl() {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/vanity-url';
        return $this->execute($url);
    }

    /**
     * Returns a PNG image widget for the guild.
     * Requires no permissions or authentication.
     * The same documentation also applies to embed.png.
     * 
     * @param   string  $style  Stil (shield, banner1, banner2, banner3, banner4)
     * @return  array
     */
    public function getGuildWidgetImage($style = 'shield') {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/widget.png?style='.$style;
        return $this->execute($url);
    }

    /////////////////////////////////////
    // Guild End
    /////////////////////////////////////

    /////////////////////////////////////
    // Invite Start
    /////////////////////////////////////

    /**
     * Returns an invite object for the given code.
     * 
     * @param   string  $inviteCode EinladungsCode
     * @param   boolean $withCounts whether the invite should contain approximate member counts
     * @return  array
     */
    public function getInvite($inviteCode, $withCounts = false) {
        $url = $this->apiUrl . '/invites/'.$inviteCode;
        if ($withCounts) {
            $url .= '?with_counts=1';
        }
        return $this->execute($url);
    }

    /**
     * Delete an invite.
     * Requires the MANAGE_CHANNELS permission on the channel this invite belongs to, or MANAGE_GUILD to remove any invite across the guild.
     * Returns an invite object on success.
     * 
     * @param   string  $inviteCode EinladungsCode
     * @return  array
     */
    public function deleteInvite($inviteCode) {
        $url = $this->apiUrl . '/invites/'.$inviteCode;
        return $this->execute($url, 'DELETE');
    }

    /////////////////////////////////////
    // Invite End
    /////////////////////////////////////

    /////////////////////////////////////
    // User Start
    /////////////////////////////////////

    /**
     * Returns the user object of the requester's account.
     * For OAuth2, this requires the identify scope, which will return the object without an email, and optionally the email scope, which returns the object with an email.
     * 
     * @return array
     */
    public function getCurrentUser() {
        $url = $this->apiUrl . '/users/@me';
        return $this->execute($url);
    }

    /**
     * Returns a user object for a given user ID.
     * 
     * @param   integer $userID ID des Benutzers
     * @return  array
     */
    public function getUser($userID) {
        $url = $this->apiUrl . '/users/'.$userID;
        return $this->execute($url);
    }

    /**
     * Modify the requester's user account settings. Returns a user object on success.
     * 
     * @param   array   $params Parameter
     * @return  array
     */
    public function modifyCurrentUser($params) {
        $url = $this->apiUrl . '/users/@me';
        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Returns a list of partial guild objects the current user is a member of.
     * Requires the guilds OAuth2 scope.
     * 
     * @return array
     */
    public function getCurrentUserGuilds($params = []) {
        $url = $this->apiUrl . '/users/@me/guilds';
        if (!empty($params)) {
            $url .= '?'.http_build_query($params, '', '&');
        }
        return $this->execute($url);
    }

    /**
     * Leave a guild.
     * Returns a 204 empty response on success.
     * 
     * @return array
     */
    public function leaveGuild() {
        $url = $thid->apiUrl . '/users/@me/guilds/'.$this->guildID;
        return $this->execute($url, 'DELETE');
    }

    /**
     * Returns a list of DM channel objects.
     * For bots, this is no longer a supported method of getting recent DMs, and will return an empty array.
     * 
     * @return array
     */
    public function getUserDMs() {
        $url = $this->apiUrl . '/users/@me/channels';
        return $this->execute($url);
    }

    /**
     * Create a new DM channel with a user.
     * Returns a DM channel object.
     * 
     * @param   integer $recipientID    ID des Empfängers
     * @return  array
     */
    public function createDM($recipientID) {
        $url = $this->apiUrl . '/users/@me/channels';
        $params = [
            'recipient_id' => $recipientID
        ];
        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Create a new group DM channel with multiple users.
     * Returns a DM channel object.
     * This endpoint was intended to be used with the now-deprecated GameBridge SDK.
     * DMs created with this endpoint will not be shown in the Discord client
     * 
     * @param   array   $params Parameter
     * @return  array
     */
    public function createGroupDM($params) {
        $url = $this->apiUrl . '/users/@me/channels';
        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Returns a list of connection objects.
     * Requires the connections OAuth2 scope.
     * 
     * @return  array
     */
    public function getUserConnections() {
        $url = $this->apiUrl . '/users/@me/connections';
        return $this->execute($url);
    }

    /////////////////////////////////////
    // User End
    /////////////////////////////////////

    /////////////////////////////////////
    // Voice Start
    /////////////////////////////////////

    /**
     * Returns an array of voice region objects that can be used when creating servers.
     * 
     * @return  array
     */
    public function listVoiceRegions() {
        $url = $this->apiUrl . '/voice/regions';
        return $this->execute($url);
    }

    /////////////////////////////////////
    // Voice End
    /////////////////////////////////////

    /////////////////////////////////////
    // Webhook Start
    /////////////////////////////////////

    /**
     * Create a new webhook.
     * Requires the MANAGE_WEBHOOKS permission.
     * Returns a webhook object on success.
     * 
     * @param   integer $channelID  ID des Channels
     * @param   string  $name       Name des Webhooks
     * @param   string  $avatar     Avatar des Webhooks
     * @return  array
     */
    public function createWebhook($channelID, $name, $avatar = null) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/webhooks';
        $params = [
            'name' => $name
        ];
        if (!empty($avatar)) {
            $params['avatar'] = $avatar;
        }
        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Returns a list of channel webhook objects.
     * Requires the MANAGE_WEBHOOKS permission.
     * 
     * @param   integer $channelID  ID des Channels
     * @return  array
     */
    public function getChannelWebhooks($channelID) {
        $url = $this->apiUrl . '/channels/'.$channelID.'/webhooks';
        return $this->execute($url);
    }

    /**
     * Returns a list of guild webhook objects.
     * Requires the MANAGE_WEBHOOKS permission.
     * 
     * @return array
     */
    public function getGuildWebhooks() {
        $url = $this->apiUrl . '/guilds/'.$this->guildID.'/webhooks';
        return $this->execute($url);
    }

    /**
     * Returns the new webhook object for the given id.
     * 
     * @param   integer $webhookID  ID des Webhooks
     * @return  array
     */
    public function getWebhook($webhookID) {
        $url = $this->apiUrl . '/webhooks/'.$webhookID;
        return $this->execute($url);
    }

    /**
     * Same as above, except this call does not require authentication and returns no user in the webhook object.
     * 
     * @param   integer $webhookID      ID des Webhooks
     * @param   string  $webhookToken   Token des Webhooks
     * @return  array
     */
    public function getWebhookWithToken($webhookID, $webhookToken) {
        $url = $this->apiUrl . '/webhooks/'.$webhookID.'/'.$webhookToken;
        return $this->execute($url);
    }

    /**
     * Modify a webhook.
     * Requires the MANAGE_WEBHOOKS permission.
     * Returns the updated webhook object on success.
     * 
     * @param   integer $webhookID  ID des Webhooks
     * @param   array   $params     Parameter
     * @return  array
     */
    public function modifyWebhook($webhookID, $params) {
        $url = $this->apiUrl . '/webhooks/'.$webhookID;
        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Same as above, except this call does not require authentication, does not accept a channel_id parameter in the body, and does not return a user in the webhook object.
     * 
     * @param   integer $webhookID      ID des Webhooks
     * @param   string  $webhookToken   Token des Webhooks
     * @param   array   $params         Parameter
     * @return  array
     */
    public function modifyWebhookWithToken($webhookID, $webhookToken, $params) {
        $url = $this->apiUrl . '/webhooks/'.$webhookID.'/'.$webhookToken;
        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Delete a webhook permanently.
     * User must be owner.
     * Returns a 204 NO CONTENT response on success.
     * 
     * @param   integer $webhookID  ID des Webhooks
     * @return  array
     */
    public function deleteWebhook($webhookID) {
        $url = $this->apiUrl . '/webhooks/'.$webhookID;
        return $this->execute($url, 'DELETE');
    }

    /**
     * Same as above, except this call does not require authentication.
     * 
     * @param   integer $webhookID      ID des Webhooks
     * @param   string  $webhookToken   Token des Webhooks
     * @return  array
     */
    public function deleteWebhookWithToken($webhookID, $webhookToken) {
        $url = $this->apiUrl . '/webhooks/'.$webhookID.'/'.$webhookToken;
        return $this->execute($url, 'DELETE');
    }

    /**
     * executes a webhook
     * 
     * @param   integer $webhookID      ID des Webhooks
     * @param   string  $webhookToken   Token des Webhooks
     * @param   array   $params         Parameter
     * @param   boolean $wait           waits for server confirmation of message send before response, and returns the created message body (defaults to false; when false a message that is not saved does not return an error)
     * @return  array
     */
    public function executeWebhook($webhookID, $webhookToken, $params, $wait = false) {
        $url = $this->apiUrl . '/webhooks/'.$webhookID.'/'.$webhookToken;
        if ($wait) {
            $url .= '?wait=true';
        }
        $contentType = 'application/json';
        if (isset($params['file'])) {
            $contentType = 'multipart/form-data';
        }
        return $this->execute($url, 'POST', $params, $contentType);
    }

    /**
     * Refer to Slack's documentation for more information. We do not support Slack's channel, icon_emoji, mrkdwn, or mrkdwn_in properties.
     * 
     * @param   integer $webhookID      ID des Webhooks
     * @param   string  $webhookToken   Token des Webhooks
     * @param   array   $params         Parameter
     * @param   boolean $wait           waits for server confirmation of message send before response, and returns the created message body (defaults to false; when false a message that is not saved does not return an error)
     * @return  array
     */
    public function executeSlackCompatibleWebhook($webhookID, $webhookToken, $params, $wait = false) {
        $url = $this->apiUrl . '/webhooks/'.$webhookID.'/'.$webhookToken.'/slack';
        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Add a new webhook to your GitHub repo (in the repo's settings), and use this endpoint as the "Payload URL." You can choose what events your Discord channel receives by choosing the "Let me select individual events" option and selecting individual events for the new webhook you're configuring.
     * 
     * @param   integer $webhookID      ID des Webhooks
     * @param   string  $webhookToken   Token des Webhooks
     * @param   array   $params         Parameter
     * @param   boolean $wait           waits for server confirmation of message send before response, and returns the created message body (defaults to false; when false a message that is not saved does not return an error)
     * @return  array
     */
    public function executeGithubCompatibleWebhook($webhookID, $webhookToken, $params, $wait = false) {
        $url = $this->apiUrl . '/webhooks/'.$webhookID.'/'.$webhookToken.'/github';
        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /////////////////////////////////////
    // Webhook End
    /////////////////////////////////////

    /////////////////////////////////////
    // OAuth2 Start
    /////////////////////////////////////

    /**
     * methode to gnerate OAuth2 Link
     * 
     * @param   integer $clientID       ID der Awnedung
     * @param   array   $scope          Scope-Inhalt, was Benutzer alles authorisieren soll
     * @param   string  $redirectUri    die Redirect-URI der Website
     * @return  string
     */
    public function oauth2Authorize($clientID, $scope, $redirectUri, $state = null) {
        $url = $this->apiUrl . '/oauth2/authorize?response_type=code&client_id='.$clientID.'&';
        $params = [
            'scope' => implode(' ', $scope),
            'redirect_uri' => $redirectUri
        ];
        if ($state !== null) {
            $params['state'] = $state;
        }
        $url .= http_build_query($params, '', '&');
        return $url;
    }

    /**
     * Verifiziert den Code
     * 
     * @param   integer $clientID       ID der Awnedung
     * @param   string  $clientSecret   Geheimer Schlüssel der Anwendung
     * @param   string  $code           OAuth2-Code
     * @param   string  $redirectUri    die Redirect-URI der Website
     * @param   string  $grantType      Grant-Type (authorization_code, refresh_token, client_credentials)
     * @return  array
     */
    public function oauth2Token($clientID, $clientSecret, $code, $redirectUri, $grantType = 'authorization_code') {
        $url = $this->apiUrl . '/oauth2/token';
        $params = [
            'client_id' => $clientID,
			'client_secret' => $clientSecret,
			'grant_type' => $grantType,
			'code' => $code,
			'redirect_uri' => $redirectUri
        ];
        if ($grantType == 'refresh_token') {
            $params['refresh_token'] = $code;
        } else {
            $params['code'] = $code;
        }
        return $this->execute($url, 'POST', $params);
    }
    
    /**
     * Returns the bot's OAuth2 application info.
     * 
     * @return array
     */
    public function getCurrentApplicationInformation() {
        $url = $this->apiUrl . '/oauth2/applications/@me';
        return $this->execute($url);
    }

    /////////////////////////////////////
    // OAuth2 End
    /////////////////////////////////////

    /////////////////////////////////////
    // Gateway Start
    /////////////////////////////////////

    /**
     * Returns an object with a single valid WSS URL, which the client can use for Connecting. Clients should cache this value and only call this endpoint to retrieve a new URL if they are unable to properly establish a connection using the cached version of the URL.
     * 
     * @return array
     */
    public function getGateway() {
        $url = $this->apiUrl . '/gateway';
        return $this->execute($url);
    }

    /**
     * Returns an object based on the information in Get Gateway, plus additional metadata that can help during the operation of large or sharded bots. Unlike the Get Gateway, this route should not be cached for extended periods of time as the value is not guaranteed to be the same per-call, and changes as the bot joins/leaves guilds.
     * 
     * @return array
     */
    public function getGatewayBot() {
        $url = $this->apiUrl . '/gateway/bot';
        return $this->execute($url);
    }

    /////////////////////////////////////
    // Gateway End
    /////////////////////////////////////

    /////////////////////////////////////
    // Decoder Start
    /////////////////////////////////////

    /**
     * returns encoded user flag informations
     * 
     * @param   integer $flag       Benutzer Flag als Decimalwert
     * @return array
     */
    public function getUserFlagsArray($flag) {
        $userFlags = [
            1 => 'Discord Employee', // 1 << 0
            2 => 'Discord Partner', // 1 << 1
            4 => 'HypeSquad Events', // 1 << 2
            8 => 'Bug Hunter Level 1', // 1 << 3
            64 => 'House Bravery', // 1 << 6
            128 => 'House Brilliance', // 1 << 7
            256 => 'House Balance', // 1 << 8
            512 => 'Early Supporter', // 1 << 9
            1024 => 'Team User', // 1 << 10
            4096 => 'System', // 1 << 12
            16384 => 'Bug Hunter Level 2', // 1 << 14
        ];
        $flags = [];

        foreach ($userFlags as $userFlag => $description) {
            if (($flag & $userFlag) == $userFlag) {
                $flags[] = $description;
            }
        }

        return $flags;
    }

    /**
     * returns encoded snowflake informations
     * 
     * @param   integer $snowflake  Snowflake ID as decimal
     * @return array
     */
    public function decodeSnowflake($snowflake) {
        return [
            'timestamp' => round((($snowflake >> 22) + 1420070400000) / 1000),
            'internalWorkerID' => ($snowflake & 0x3E0000) >> 17,
            'internalProcessID' => ($snowflake & 0x1F000) >> 12,
            'increment' => $snowflake & 0xFFF
        ];
    }

    /**
     * returns encoded permissions informations
     * 
     * @param   integer $snowflake  Snowflake ID as decimal
     * @return array
     */
    public function permissionDecoder($permissions) {
        $permissionFlags = [
            0x1 => 'CREATE_INSTANT_INVITE',
            0x2 => 'KICK_MEMBERS',
            0x4 => 'BAN_MEMBERS',
            0x8 => 'ADMINISTRATOR',
            0x10 => 'MANAGE_CHANNELS',
            0x20 => 'MANAGE_GUILD',
            0x40 => 'ADD_REACTIONS',
            0x80 => 'VIEW_AUDIT_LOG',
            0x400 => 'VIEW_CHANNEL',
            0x800 => 'SEND_MESSAGES',
            0x1000 => 'SEND_TTS_MESSAGES',
            0x2000 => 'MANAGE_MESSAGES',
            0x4000 => 'EMBED_LINKS',
            0x8000 => 'ATTACH_FILES',
            0x10000 => 'READ_MESSAGE_HISTORY',
            0x20000 => 'MENTION_EVERYONE',
            0x40000 => 'USE_EXTERNAL_EMOJIS',
            0x80000 => 'VIEW_GUILD_INSIGHTS',
            0x100000 => 'CONNECT',
            0x200000 => 'SPEAK',
            0x400000 => 'MUTE_MEMBERS',
            0x800000 => 'DEAFEN_MEMBERS',
            0x1000000 => 'MOVE_MEMBERS',
            0x2000000 => 'USE_VAD',
            0x100 => 'PRIORITY_SPEAKER',
            0x200 => 'STREAM',
            0x4000000 => 'CHANGE_NICKNAME',
            0x8000000 => 'MANAGE_NICKNAMES',
            0x10000000 => 'MANAGE_ROLES',
            0x20000000 => 'MANAGE_WEBHOOKS',
            0x40000000 => 'MANAGE_EMOJIS'
        ];

        $flags = [];
        foreach ($permissionFlags as $permissionFlag => $description) {
            if (($permissions & $permissionFlag) == $permissionFlag) {
                $flags[] = $description;
            }
        }

        return $flags;
    }

    /////////////////////////////////////
    // Decoder End
    /////////////////////////////////////

    /**
     * führt eine API-Anfrage aus
     * 
     * @param   string  $url            URL der API-Anfrage
     * @param   string  $method         HTTP-Methode (Standard: GET)
     * @param   array   $parameters     Informationen die per Post oder JSON-Objekt an die API gesendet werden soll
     * @param   string  $contentType    Sendungstyp
     * @return  array
     */
    protected function execute($url, $method = 'GET', $parameters = [], $contentType = 'application/x-www-form-urlencoded') {
        $options = [
            'method' => $method,
            'timeout' => 2
        ];
        if ($contentType == 'application/json') {
            $parameters = JSON::encode($parameters);
        }
        $request = new HTTPRequest($url, $options, $parameters);
        $request->addHeader('authorization', $this->botType.' '.$this->botToken);

        if ($method !== 'GET') {
            if (empty($parameters)) {
                $request->addHeader('content-length', '0');
            }
            $request->addHeader('content-type', $contentType);
        }

        $reply = [];
        try {
            $request->execute();
            $reply = $this->parseReply($request->getReply());
        } catch (HTTPNotFoundException $e) {
            $reply = $this->parseReply($request->getReply());
            $reply['error'] = [
                'message' => $e->getMessage(),
                'status' => $e->getCode(),
                'url' => $url,
                'method' => $method,
                'parameters' => $parameters,
                'contentType' => $contentType,
                'guildID' => $this->guildID,
                'botToken' => $this->botToken,
                'botType' => $this->botType
            ];
        } catch (HTTPServerErrorException $e) {
            $reply = $this->parseReply($request->getReply());
            $reply['error'] = [
                'message' => $e->getMessage(),
                'status' => $e->getCode(),
                'url' => $url,
                'method' => $method,
                'parameters' => $parameters,
                'contentType' => $contentType,
                'guildID' => $this->guildID,
                'botToken' => $this->botToken,
                'botType' => $this->botType
            ];
        } catch (HTTPUnauthorizedException $e) {
            $reply = $this->parseReply($request->getReply());
            $reply['error'] = [
                'message' => $e->getMessage(),
                'status' => $e->getCode(),
                'url' => $url,
                'method' => $method,
                'parameters' => $parameters,
                'contentType' => $contentType,
                'guildID' => $this->guildID,
                'botToken' => $this->botToken,
                'botType' => $this->botType
            ];
        } catch (SystemException $e) {
            $reply = $this->parseReply($request->getReply());
            $reply['error'] = [
                'message' => $e->getMessage(),
                'status' => $e->getCode(),
                'url' => $url,
                'method' => $method,
                'parameters' => $parameters,
                'contentType' => $contentType,
                'guildID' => $this->guildID,
                'botToken' => $this->botToken,
                'botType' => $this->botType
            ];
        } catch (HTTPException $e) {
            $reply = $this->parseReply($request->getReply());
            $reply['error'] = [
                'message' => $e->getMessage(),
                'status' => $e->getCode(),
                'url' => $url,
                'method' => $method,
                'parameters' => $parameters,
                'contentType' => $contentType,
                'guildID' => $this->guildID,
                'botToken' => $this->botToken,
                'botType' => $this->botType
            ];
        }

        return $reply;
    }

    /**
     * verarbeitet die API antwort und fügt interessante Informationen an
     * 
     * @param   array   $replyTmp   die Antwort von der API
     * @return  array
     */
    protected function parseReply($replyTmp) {
        $body = $replyTmp['body'];
        try {
            $body = JSON::decode($body, true);
        } catch (SystemException $e) {}
        $reply = [
            'error' => false,
            'status' => $replyTmp['statusCode'],
            'body' => $body,
            'rateLimit' => false
        ];
        if (isset($replyTmp['httpHeaders']['x-ratelimit-limit'][0])) {
            $reply['rateLimit']['limit'] = $replyTmp['httpHeaders']['x-ratelimit-limit'][0];
        }
        if (isset($replyTmp['httpHeaders']['x-ratelimit-remaining'][0])) {
            $reply['rateLimit']['remaining'] = $replyTmp['httpHeaders']['x-ratelimit-remaining'][0];
        }
        if (isset($replyTmp['httpHeaders']['x-ratelimit-reset'][0])) {
            $reply['rateLimit']['reset'] = $replyTmp['httpHeaders']['x-ratelimit-reset'][0];
        }
        return $reply;
    }
}
