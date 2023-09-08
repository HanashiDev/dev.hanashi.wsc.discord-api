<?php

namespace wcf\system\discord;

use Exception;
use SensitiveParameter;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\ResponseInterface;
use wcf\data\discord\bot\DiscordBot;
use wcf\system\io\HttpFactory;
use wcf\util\JSON;

/**
 * Klasse zum Handlen der Discord-API-Aufrufe
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.eu/freie-lizenz/)
 * @package WoltLabSuite\Core\System\Discord
 */
class DiscordApi
{
    // ApplicationCommandType https://discord.com/developers/docs/interactions/application-commands#application-command-object-application-command-types
    /**
     * Slash commands; a text-based command that shows up when a user types /
     */
    public const DISCORD_COMMAND_CHAT_INPUT = 1;

    /**
     * A UI-based command that shows up when you right click or tap on a user
     */
    public const DISCORD_COMMAND_USER = 2;

    /**
     * A UI-based command that shows up when you right click or tap on a message
     */
    public const DISCORD_COMMAND_MESSAGE = 3;

    // ApplicationCommandOptionType https://discord.com/developers/docs/interactions/slash-commands#application-command-object-application-command-option-type
    public const DISCORD_SUB_COMMAND = 1;

    public const DISCORD_SUB_COMMAND_GROUP = 2;

    public const DISCORD_STRING = 3;

    public const DISCORD_INTEGER = 4;

    public const DISCORD_BOOLEAN = 5;

    public const DISCORD_USER = 6;

    public const DISCORD_CHANNEL = 7;

    public const DISCORD_ROLE = 8;

    public const DISCORD_MENTIONABLE = 9;

    public const DISCORD_NUMBER = 10;

    public const DISCORD_ATTACHMENT = 11;

    // Interaction Types
    // https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-object-interaction-type
    public const DISCORD_PING = 1;

    public const DISCORD_APPLICATION_COMMAND = 2;

    public const DISCORD_MESSAGE_COMPONENT = 3;

    public const DISCORD_APPLICATION_COMMAND_AUTOCOMPLETE = 4;

    public const DISCORD_MODAL_SUBMIT = 5;

    // Interaction Callback Type https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-response-object-interaction-callback-type
    public const DISCORD_PONG = 1;

    public const DISCORD_CHANNEL_MESSAGE_WITH_SOURCE = 4;

    public const DISCORD_DEFERRED_CHANNEL_MESSAGE_WITH_SOURCE = 5;

    public const DISCORD_DEFERRED_UPDATE_MESSAGE = 6;

    public const DISCORD_UPDATE_MESSAGE = 7;

    public const DISCORD_APPLICATION_COMMAND_AUTOCOMPLETE_RESULT = 8;

    public const DISCORD_MODAL = 9;

    // Message Flags https://discord.com/developers/docs/resources/channel#message-object-message-flags
    /**
     * this message has been published to subscribed channels (via Channel Following)
     */
    public const DISCORD_CROSSPOSTED = 1 << 0;

    /**
     * this message originated from a message in another channel (via Channel Following)
     */
    public const DISCORD_IS_CROSSPOST = 1 << 1;

    /**
     * do not include any embeds when serializing this message
     */
    public const DISCORD_SUPPRESS_EMBED = 1 << 2;

    /**
     * the source message for this crosspost has been deleted (via Channel Following)
     */
    public const DISCORD_SOURCE_MESSAGE_DELETED = 1 << 3;

    /**
     * this message came from the urgent message system
     */
    public const DISCORD_URGENT = 1 << 4;

    /**
     * this message has an associated thread, with the same id as the message
     */
    public const DISCORD_HAS_THREAD = 1 << 5;

    /**
     * this message is only visible to the user who invoked the Interaction
     */
    public const DISCORD_EPHEMERAL = 1 << 6;

    /**
     * this message is an Interaction Response and the bot is "thinking"
     */
    public const DISCORD_LOADING = 1 << 7;

    /**
     * this message failed to mention some roles and add their members to the thread
     */
    public const DISCORD_FAILED_TO_MENTION_SOME_ROLES_IN_THREAD = 1 << 8;

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
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * Konstruktor
     *
     * @param   integer $guildID        Server-ID des Discord-Servers
     * @param   string  $botToken       Geheimer Schlüssel des Discord-Bots
     * @param   string  $botType        Bot-Typ
     */
    public function __construct(
        $guildID,
        #[SensitiveParameter]
        $botToken,
        $botType = 'Bot'
    ) {
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
    public static function getApiByID($botID)
    {
        $discordBot = new DiscordBot($botID);
        if (!$discordBot->botID) {
            return null;
        }

        $discordApi = $discordBot->getDiscordApi();
        $discordApi->discordBot($discordBot);

        return $discordApi;
    }

    public function discordBot($bot)
    {
        $this->discordBot = $bot;
    }

    /////////////////////////////////////
    // Application Commands Start
    /////////////////////////////////////

    /**
     * Fetch all of the global commands for your application. Returns an array of ApplicationCommand objects.
     *
     * @param  integer   $applicationID
     * @return array
     */
    public function getGlobalApplicationCommands($applicationID, bool $withLocalizations = false)
    {
        $url = \sprintf('%s/applications/%s/commands', $this->apiUrl, $applicationID);
        if ($withLocalizations) {
            $url .= '?with_localizations=1';
        }

        return $this->execute($url);
    }

    /**
     * Create a new global command. New global commands will be available in all guilds after 1 hour. Returns 201 and
     * an ApplicationCommand object.
     *
     * @param  integer $applicationID
     * @param  array $params
     * @return array
     */
    public function createGlobalApplicationCommand($applicationID, $params)
    {
        $url = \sprintf('%s/applications/%s/commands', $this->apiUrl, $applicationID);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Fetch a global command for your application. Returns an ApplicationCommand object.
     *
     * @param  integer $applicationID
     * @param  integer $commandID
     * @return array
     */
    public function getGlobalApplicationCommand($applicationID, $commandID)
    {
        $url = \sprintf('%s/applications/%s/commands/%s', $this->apiUrl, $applicationID, $commandID);

        return $this->execute($url);
    }

    /**
     * Edit a global command. Updates will be available in all guilds after 1 hour. Returns 200 and an
     * ApplicationCommand object.
     *
     * @param  integer $applicationID
     * @param  integer $commandID
     * @param  array $params
     * @return array
     */
    public function editGlobalApplicationCommand($applicationID, $commandID, $params)
    {
        $url = \sprintf('%s/applications/%s/commands/%s', $this->apiUrl, $applicationID, $commandID);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Deletes a global command. Returns 204.
     *
     * @param  integer $applicationID
     * @param  integer $commandID
     * @return array
     */
    public function deleteGlobalApplicationCommand($applicationID, $commandID)
    {
        $url = \sprintf('%s/applications/%s/commands/%s', $this->apiUrl, $applicationID, $commandID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Fetch all of the guild commands for your application for a specific guild. Returns an array of
     * ApplicationCommand objects.
     *
     * @param  integer $applicationID
     * @param  integer $commandID
     * @return array
     */
    public function getGuildApplicationCommands($applicationID, $commandID, bool $withLocalizations = false)
    {
        $url = \sprintf('%s/applications/%s/commands/%s', $this->apiUrl, $applicationID, $commandID);
        if ($withLocalizations) {
            $url .= '?with_localizations=1';
        }

        return $this->execute($url, 'GET');
    }

    /**
     * Takes a list of application commands, overwriting existing commands that are registered globally for this
     * application. Updates will be available in all guilds after 1 hour. Returns 200 and a list of ApplicationCommand
     * objects. Commands that do not already exist will count toward daily application command create limits.
     *
     * @param  integer $applicationID
     * @return array
     */
    public function bulkOverwriteGlobalApplicationCommands($applicationID)
    {
        $url = \sprintf('%s/applications/%s/commands', $this->apiUrl, $applicationID);

        return $this->execute($url, 'PUT');
    }

    /**
     * Create a new guild command. New guild commands will be available in the guild immediately. Returns 201 and an
     * ApplicationCommand object. If the command did not already exist, it will count toward daily application command
     * create limits.
     *
     * @param  integer $applicationID
     * @param  integer $guildID
     * @param  array $params
     * @return array
     */
    public function createGuildApplicationCommand($applicationID, $guildID, $params)
    {
        $url = \sprintf('%s/applications/%s/guilds/%s/commands', $this->apiUrl, $applicationID, $guildID);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Fetch a guild command for your application. Returns an ApplicationCommand object.
     *
     * @param  integer $applicationID
     * @param  integer $guildID
     * @param  integer $commandID
     * @return array
     */
    public function getGuildApplicationCommand($applicationID, $guildID, $commandID)
    {
        $url = \sprintf(
            '%s/applications/%s/guilds/%s/commands/%s',
            $this->apiUrl,
            $applicationID,
            $guildID,
            $commandID
        );

        return $this->execute($url, 'GET');
    }

    /**
     * Edit a guild command. Updates for guild commands will be available immediately. Returns 200 and an
     * ApplicationCommand object.
     *
     * @param  integer $applicationID
     * @param  integer $guildID
     * @param  integer $commandID
     * @param  array $params
     * @return array
     */
    public function editGuildApplicationCommand($applicationID, $guildID, $commandID, $params)
    {
        $url = \sprintf(
            '%s/applications/%s/guilds/%s/commands/%s',
            $this->apiUrl,
            $applicationID,
            $guildID,
            $commandID
        );

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Delete a guild command. Returns 204 on success.
     *
     * @param  integer $applicationID
     * @param  integer $guildID
     * @param  integer $commandID
     * @return array
     */
    public function deleteGuildApplicationCommand($applicationID, $guildID, $commandID)
    {
        $url = \sprintf(
            '%s/applications/%s/guilds/%s/commands/%s',
            $this->apiUrl,
            $applicationID,
            $guildID,
            $commandID
        );

        return $this->execute($url, 'DELETE');
    }

    /**
     * Takes a list of application commands, overwriting existing commands for the guild. Returns 200 and a list of
     * ApplicationCommand objects.
     *
     * @param  integer $applicationID
     * @param  integer $guildID
     * @return array
     */
    public function bulkOverwriteGuildApplicationCommands($applicationID, $guildID)
    {
        $url = \sprintf('%s/applications/%s/guilds/%s/commands', $this->apiUrl, $applicationID, $guildID);

        return $this->execute($url, 'PUT');
    }

    /**
     * Create a response to an Interaction from the gateway. Takes an Interaction response.
     *
     * @param  integer $interactionID
     * @param  string $interactionToken
     * @param  array $params
     * @return array
     */
    public function createInteractionResponse(
        $interactionID,
        #[SensitiveParameter]
        $interactionToken,
        $params
    ) {
        $url = \sprintf('%s/interactions/%s/%s/callback', $this->apiUrl, $interactionID, $interactionToken);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Returns the initial Interaction response. Functions the same as Get Webhook Message.
     *
     * @param  integer $applicationID
     * @param  string $interactionToken
     * @return array
     */
    public function getOriginalInteractionResponse(
        $applicationID,
        #[SensitiveParameter]
        $interactionToken
    ) {
        $url = \sprintf('%s/webhooks/%s/%s/messages/@original', $this->apiUrl, $applicationID, $interactionToken);

        return $this->execute($url, 'GET');
    }

    /**
     * Edits the initial Interaction response. Functions the same as Edit Webhook Message.
     *
     * @param  integer $applicationID
     * @param  string $interactionToken
     * @param  array $params
     * @return array
     */
    public function editOriginalInteractionResponse(
        $applicationID,
        #[SensitiveParameter]
        $interactionToken,
        $params
    ) {
        $url = \sprintf('%s/webhooks/%s/%s/messages/@original', $this->apiUrl, $applicationID, $interactionToken);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Deletes the initial Interaction response. Returns 204 on success.
     *
     * @param  integer $applicationID
     * @param  string $interactionToken
     * @return array
     */
    public function deleteOriginalInteractionResponse(
        $applicationID,
        #[SensitiveParameter]
        $interactionToken
    ) {
        $url = \sprintf('%s/webhooks/%s/%s/messages/@original', $this->apiUrl, $applicationID, $interactionToken);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Create a followup message for an Interaction. Functions the same as Execute Webhook, but wait is always true,
     * and flags can be set to 64 in the body to send an ephemeral message. The thread_id query parameter is not
     * required (and is furthermore ignored) when using this endpoint for interaction followups.
     *
     * @param  integer $applicationID
     * @param  string $interactionToken
     * @param  array $params
     * @return array
     */
    public function createFollowupMessage(
        $applicationID,
        #[SensitiveParameter]
        $interactionToken,
        $params
    ) {
        $url = \sprintf('%s/webhooks/%s/%s', $this->apiUrl, $applicationID, $interactionToken);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Edits a followup message for an Interaction. Functions the same as Edit Webhook Message.
     *
     * @param  integer $applicationID
     * @param  string $interactionToken
     * @param  integer $messageID
     * @param  array $params
     * @return array
     */
    public function editFollowupMessage(
        $applicationID,
        #[SensitiveParameter]
        $interactionToken,
        $messageID,
        $params
    ) {
        $url = \sprintf('%s/webhooks/%s/%s/messages/%s', $this->apiUrl, $applicationID, $interactionToken, $messageID);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Deletes a followup message for an Interaction. Returns 204 on success.
     *
     * @param  integer $applicationID
     * @param  string $interactionToken
     * @param  integer $messageID
     * @return array
     */
    public function deleteFollowupMessage(
        $applicationID,
        #[SensitiveParameter]
        $interactionToken,
        $messageID
    ) {
        $url = \sprintf('%s/webhooks/%s/%s/messages/%s', $this->apiUrl, $applicationID, $interactionToken, $messageID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Fetches command permissions for all commands for your application in a guild. Returns an array of
     * GuildApplicationCommandPermissions objects.
     *
     * @param  integer $applicationID
     * @param  integer $guildID
     * @return array
     */
    public function getGuildApplicationCommandPermissions($applicationID, $guildID)
    {
        $url = \sprintf('%s/applications/%s/guilds/%s/commands/permissions', $this->apiUrl, $applicationID, $guildID);

        return $this->execute($url, 'GET');
    }

    /**
     * Fetches command permissions for a specific command for your application in a guild. Returns a
     * GuildApplicationCommandPermissions object.
     *
     * @param  integer $applicationID
     * @param  integer $guildID
     * @param  integer $commandID
     * @return array
     */
    public function getApplicationCommandPermissions($applicationID, $guildID, $commandID)
    {
        $url = \sprintf(
            '%s/applications/%s/guilds/%s/commands/%s/permissions',
            $this->apiUrl,
            $applicationID,
            $guildID,
            $commandID
        );

        return $this->execute($url, 'GET');
    }

    /**
     * Edits command permissions for a specific command for your application in a guild. You can only add up to 10
     * permission overwrites for a command. Returns a GuildApplicationCommandPermissions object.
     *
     * This endpoint will overwrite existing permissions for the command in that guild
     *
     * Deleting or renaming a command will permanently delete all permissions for that command
     *
     * @param  integer $applicationID
     * @param  integer $guildID
     * @param  integer $commandID
     * @param  array $permissions
     * @return array
     */
    public function editApplicationCommandPermissions($applicationID, $guildID, $commandID, $permissions)
    {
        $url = \sprintf(
            '%s/applications/%s/guilds/%s/commands/%s/permissions',
            $this->apiUrl,
            $applicationID,
            $guildID,
            $commandID
        );

        return $this->execute($url, 'PUT', ['permissions' => $permissions], 'application/json');
    }

    /**
     * batchEditApplicationCommandPermissions
     *
     * @param  integer $applicationID
     * @param  integer $guildID
     * @param  array $params
     * @return array
     */
    public function batchEditApplicationCommandPermissions($applicationID, $guildID, $params)
    {
        $url = \sprintf('%s/applications/%s/guilds/%s/commands/permissions', $this->apiUrl, $applicationID, $guildID);

        return $this->execute($url, 'PUT', $params, 'application/json');
    }

    /**
     * verify a request from discord webhook
     *
     * @param  string $publicKey
     * @param  string $body
     * @return boolean
     */
    public static function verifyRequest($publicKey, $body)
    {
        if (empty($_SERVER['HTTP_X_SIGNATURE_ED25519'])) {
            return false;
        }
        if (empty($_SERVER['HTTP_X_SIGNATURE_TIMESTAMP'])) {
            return false;
        }

        $publicKey = \sodium_hex2bin($publicKey);
        $signature = \sodium_hex2bin($_SERVER['HTTP_X_SIGNATURE_ED25519']);
        $timestamp = $_SERVER['HTTP_X_SIGNATURE_TIMESTAMP'];

        if (!\sodium_crypto_sign_verify_detached($signature, $timestamp . $body, $publicKey)) {
            return false;
        }

        return true;
    }

    /////////////////////////////////////
    // Application Commands End
    /////////////////////////////////////

    /////////////////////////////////////
    // Audit Log Start
    /////////////////////////////////////

    /**
     * Returns an audit log object for the guild.
     * Requires the 'VIEW_AUDIT_LOG' permission.
     *
     * @return array
     */
    public function getGuildAuditLog($params = [])
    {
        $url = \sprintf('%s/guilds/%s/audit-logs', $this->apiUrl, $this->guildID);
        if (!empty($params)) {
            $url .= '?' . \http_build_query($params, '', '&');
        }

        return $this->execute($url);
    }

    /////////////////////////////////////
    // Audit Log End
    /////////////////////////////////////

    /////////////////////////////////////
    // Auto Moderation Start
    /////////////////////////////////////

    /**
     * Get a list of all rules currently configured for the guild. Returns a list of auto moderation rule objects for
     * the given guild.
     *
     * This endpoint requires the MANAGE_GUILD permission.
     *
     * @return array
     */
    public function listAutoModerationRulesForGuild()
    {
        $url = \sprintf('%s/guilds/%s/auto-moderation/rules', $this->apiUrl, $this->guildID);

        return $this->execute($url);
    }

    /**
     * Get a single rule. Returns an auto moderation rule object.
     *
     * This endpoint requires the MANAGE_GUILD permission.
     *
     * @param  int $ruleID
     * @return array
     */
    public function getAutoModerationRule($ruleID)
    {
        $url = \sprintf('%s/guilds/%s/auto-moderation/rules/%s', $this->apiUrl, $this->guildID, $ruleID);

        return $this->execute($url);
    }

    /**
     * Create a new rule. Returns an auto moderation rule on success. Fires an Auto Moderation Rule Create Gateway
     * event.
     *
     * This endpoint requires the MANAGE_GUILD permission.
     *
     * This endpoint supports the X-Audit-Log-Reason header.
     *
     * @param  array $params
     * @return array
     */
    public function createAutoModerationRule(array $params)
    {
        $url = \sprintf('%s/guilds/%s/auto-moderation/rules', $this->apiUrl, $this->guildID);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Modify an existing rule. Returns an auto moderation rule on success. Fires an Auto Moderation Rule Update
     * Gateway event.
     *
     * Requires MANAGE_GUILD permissions.
     *
     * All parameters for this endpoint are optional.
     *
     * This endpoint supports the X-Audit-Log-Reason header.
     *
     * @param  int $ruleID
     * @param  array $params
     * @return array
     */
    public function modifyAutoModerationRule($ruleID, array $params)
    {
        $url = \sprintf('%s/guilds/%s/auto-moderation/rules/%s', $this->apiUrl, $this->guildID, $ruleID);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Delete a rule. Returns a 204 on success. Fires an Auto Moderation Rule Delete Gateway event.
     *
     * This endpoint requires the MANAGE_GUILD permission.
     *
     * This endpoint supports the X-Audit-Log-Reason header.
     *
     * @param  int $ruleID
     * @return array
     */
    public function deleteAutoModerationRule($ruleID)
    {
        $url = \sprintf('%s/guilds/%s/auto-moderation/rules/%s', $this->apiUrl, $this->guildID, $ruleID);

        return $this->execute($url, 'DELETE');
    }

    /////////////////////////////////////
    // Auto Moderation End
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
    public function getChannel($channelID)
    {
        $url = \sprintf('%s/channels/%s', $this->apiUrl, $channelID);

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
    public function modifyChannel($channelID, $params)
    {
        $url = \sprintf('%s/channels/%s', $this->apiUrl, $channelID);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Delete a channel, or close a private message.
     * Requires the MANAGE_CHANNELS permission for the guild.
     * Deleting a category does not delete its child channels; they will have their parent_id removed and a Channel
     * Update Gateway event will fire for each of them.
     * Returns a channel object on success.
     * Fires a Channel Delete Gateway event.
     *
     * @param   integer $channelID  Channel-ID
     * @return  array
     */
    public function deleteChannel($channelID)
    {
        $url = \sprintf('%s/channels/%s', $this->apiUrl, $channelID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * alias for deleteChannel
     * @see self::deleteChannel()
     */
    public function closeChannel($channelID)
    {
        return $this->deleteChannel($channelID);
    }

    /**
     * Returns the messages for a channel.
     * If operating on a guild channel, this endpoint requires the VIEW_CHANNEL permission to be present on the current
     * user.
     * If the current user is missing the 'READ_MESSAGE_HISTORY' permission in the channel then this will return no
     * messages (since they cannot read the message history).
     * Returns an array of message objects on success.
     *
     * @param   integer $channelID  Channel-ID
     * @param   array   $params     HTTP-Parameter
     * @return  array
     */
    public function getChannelMessages($channelID, $params = [])
    {
        $url = \sprintf('%s/channels/%s/messages', $this->apiUrl, $channelID);
        if (!empty($params)) {
            $url .= '?' . \http_build_query($params, '', '&');
        }

        return $this->execute($url);
    }

    /**
     * Returns a specific message in the channel.
     * If operating on a guild channel, this endpoint requires the 'READ_MESSAGE_HISTORY' permission to be present on
     * the current user. Returns a message object on success.
     *
     * @param   integer $channelID  Channel-ID
     * @param   integer $messageID  ID der Nachricht
     *
     * @return  array
     */
    public function getChannelMessage($channelID, $messageID)
    {
        $url = \sprintf('%s/channels/%s/messages/%s', $this->apiUrl, $channelID, $messageID);

        return $this->execute($url);
    }

    /**
     * Post a message to a guild text or DM channel.
     * If operating on a guild channel, this endpoint requires the SEND_MESSAGES permission to be present on the
     * current user.
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
    public function createMessage($channelID, $params)
    {
        $url = \sprintf('%s/channels/%s/messages', $this->apiUrl, $channelID);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Crosspost a message in a News Channel to following channels. This endpoint requires the 'SEND_MESSAGES'
     * permission, if the current user sent the message, or additionally the 'MANAGE_MESSAGES' permission, for all
     * other messages, to be present for the current user.
     *
     * Returns a message object.
     *
     * @param  integer $channelID
     * @param  integer $messageID
     * @return array
     */
    public function crosspostMessage($channelID, $messageID)
    {
        $url = \sprintf('%s/channels/%s/messages/%s/crosspost', $this->apiUrl, $channelID, $messageID);

        return $this->execute($url, 'POST');
    }

    /**
     * Create a reaction for the message.
     * emoji takes the form of name:id for custom guild emoji, or Unicode characters.
     * This endpoint requires the 'READ_MESSAGE_HISTORY' permission to be present on the current user.
     * Additionally, if nobody else has reacted to the message using this emoji, this endpoint requires the
     * 'ADD_REACTIONS' permission to be present on the current user.
     * Returns a 204 empty response on success.
     *
     * @param   integer $channelID  Channel-ID
     * @param   integer $messageID  ID der Nachricht
     * @param   integer $emoji      ID des Emoji oder Unicode des Emoji
     * @return  array
     */
    public function createReaction($channelID, $messageID, $emoji)
    {
        $url = \sprintf('%s/channels/%s/messages/%s/reactions/%s/@me', $this->apiUrl, $channelID, $messageID, $emoji);

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
    public function deleteOwnReaction($channelID, $messageID, $emoji)
    {
        $url = \sprintf('%s/channels/%s/messages/%s/reactions/%s/@me', $this->apiUrl, $channelID, $messageID, $emoji);

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
    public function deleteUserReaction($channelID, $messageID, $emoji, $userID)
    {
        $url = \sprintf(
            '%s/channels/%s/messages/%s/reactions/%s/%s',
            $this->apiUrl,
            $channelID,
            $messageID,
            $emoji,
            $userID
        );

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
    public function getReactions($channelID, $messageID, $emoji, $params = [])
    {
        $url = \sprintf('%s/channels/%s/messages/%s/reactions/%s', $this->apiUrl, $channelID, $messageID, $emoji);
        if (!empty($params)) {
            $url .= '?' . \http_build_query($params, '', '&');
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
    public function deleteAllReactions($channelID, $messageID)
    {
        $url = \sprintf('%s/channels/%s/messages/%s/reactions', $this->apiUrl, $channelID, $messageID);

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
    public function editMessage($channelID, $messageID, $params)
    {
        $url = \sprintf('%s/channels/%s/messages/%s', $this->apiUrl, $channelID, $messageID);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Delete a message.
     * If operating on a guild channel and trying to delete a message that was not sent by the current user, this
     * endpoint requires the MANAGE_MESSAGES permission.
     * Returns a 204 empty response on success.
     * Fires a Message Delete Gateway event.
     *
     * @param   integer $channelID  Channel-ID
     * @param   integer $messageID  ID der Nachricht
     * @return  array
     */
    public function deleteMessage($channelID, $messageID)
    {
        $url = \sprintf('%s/channels/%s/messages/%s', $this->apiUrl, $channelID, $messageID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Delete multiple messages in a single request.
     * This endpoint can only be used on guild channels and requires the MANAGE_MESSAGES permission.
     * Returns a 204 empty response on success.
     * Fires multiple Message Delete Gateway events.
     * Any message IDs given that do not exist or are invalid will count towards the minimum and maximum message count
     * (currently 2 and 100 respectively).
     * Additionally, duplicated IDs will only be counted once.
     * This endpoint will not delete messages older than 2 weeks, and will fail if any message provided is older than
     * that.
     *
     * @param   integer $channelID  Channel-ID
     * @param   array   $messageIDs IDs von Nachrichten
     * @return  array
     */
    public function bulkDeleteMessage($channelID, $messageIDs)
    {
        $url = \sprintf('%s/channels/%s/messages/bulk-delete', $this->apiUrl, $channelID);

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
    public function editChannelPermissions($channelID, $overwriteID, $params)
    {
        $url = \sprintf('%s/channels/%s/permissions/%s', $this->apiUrl, $channelID, $overwriteID);

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
    public function getChannelInvites($channelID)
    {
        $url = \sprintf('%s/channels/%s/invites', $this->apiUrl, $channelID);

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
    public function createChannelInvite($channelID, $params = [])
    {
        $url = \sprintf('%s/channels/%s/invites', $this->apiUrl, $channelID);

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
    public function deleteChannelPermission($channelID, $overwriteID)
    {
        $url = \sprintf('%s/channels/%s/permissions/%s', $this->apiUrl, $channelID, $overwriteID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Follow a News Channel to send messages to a target channel. Requires the MANAGE_WEBHOOKS permission in the
     * target channel. Returns a followed channel object.
     *
     * @param  integer $channelID
     * @param  integer $webhookChannelID
     * @return array
     */
    public function followNewsChannel($channelID, $webhookChannelID)
    {
        $url = \sprintf('%s/channels/%s/followers', $this->apiUrl, $channelID);

        return $this->execute($url, 'POST', ['webhook_channel_id' => $webhookChannelID], 'application/json');
    }

    /**
     * Post a typing indicator for the specified channel.
     * Generally bots should not implement this route.
     * However, if a bot is responding to a command and expects the computation to take a few seconds, this endpoint
     * may be called to let the user know that the bot is processing their message.
     * Returns a 204 empty response on success.
     * Fires a Typing Start Gateway event.
     *
     * @param   integer $channelID      Channel-ID
     * @return  array
     */
    public function triggerTypingIndicator($channelID)
    {
        $url = \sprintf('%s/channels/%s/typing', $this->apiUrl, $channelID);

        return $this->execute($url, 'POST');
    }

    /**
     * Returns all pinned messages in the channel as an array of message objects.
     *
     * @param   integer $channelID      Channel-ID
     * @return  array
     */
    public function getPinnedMessages($channelID)
    {
        $url = \sprintf('%s/channels/%s/pins', $this->apiUrl, $channelID);

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
    public function pinMessage($channelID, $messageID)
    {
        $url = \sprintf('%s/channels/%s/pins/%s', $this->apiUrl, $channelID, $messageID);

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
    public function unpinMessage($channelID, $messageID)
    {
        $url = \sprintf('%s/channels/%s/pins/%s', $this->apiUrl, $channelID, $messageID);

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
    public function groupDMAddRecipient($channelID, $userID, $params = [])
    {
        $url = \sprintf('%s/channels/%s/recipients/%s', $this->apiUrl, $channelID, $userID);

        return $this->execute($url, 'PUT', $params, 'application/json');
    }

    /**
     * Removes a recipient from a Group DM
     *
     * @param   integer $channelID  Channel-ID
     * @param   integer $userID     User that should join
     * @return  array
     */
    public function groupDMRemoveRecipient($channelID, $userID)
    {
        $url = \sprintf('%s/channels/%s/recipients/%s', $this->apiUrl, $channelID, $userID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Creates a new thread from an existing message. Returns a channel on success, and a 400 BAD REQUEST on invalid
     * parameters. Fires a Thread Create Gateway event.
     *
     * When called on a GUILD_TEXT channel, creates a PUBLIC_THREAD. When called on a GUILD_ANNOUNCEMENT channel,
     * creates a ANNOUNCEMENT_THREAD. Does not work on a GUILD_FORUM channel. The id of the created thread will be the
     * same as the id of the source message, and as such a message can only have a single thread created from it.
     *
     * @param  integer $channelID
     * @param  integer $messageID
     * @param  array $params
     * @return array
     */
    public function startThreadFromMessage($channelID, $messageID, $params)
    {
        $url = \sprintf('%s/channels/%s/messages/%s/threads', $this->apiUrl, $channelID, $messageID);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Creates a new thread that is not connected to an existing message. The created thread defaults to a
     * GUILD_PRIVATE_THREAD*. Returns a channel on success, and a 400 BAD REQUEST on invalid parameters. Fires a Thread
     * Create Gateway event.
     *
     * Creating a private thread requires the server to be boosted. The guild features will indicate if that is
     * possible for the guild.
     *
     * The 3 day and 7 day archive durations require the server to be boosted. The guild features will indicate if that
     * is possible for the guild.
     *
     * type defaults to PRIVATE_THREAD in order to match the behavior when thread documentation was first published.
     * This is a bit of a weird default though, and thus is highly likely to change in a future API version, so we
     * would recommend always explicitly setting the type parameter.
     *
     * @param  integer $channelID
     * @param  array $params
     * @return array
     */
    public function startThreadWithoutMessage($channelID, $params)
    {
        $url = \sprintf('%s/channels/%s/threads', $this->apiUrl, $channelID);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Creates a new thread in a forum channel, and sends a message within the created thread. Returns a channel, with
     * a nested message object, on success, and a 400 BAD REQUEST on invalid parameters. Fires a Thread Create and
     * Message Create Gateway event.
     *
     * - The type of the created thread is PUBLIC_THREAD.
     * - See message formatting for more information on how to properly format messages.
     * - The current user must have the SEND_MESSAGES permission (CREATE_PUBLIC_THREADS is ignored).
     * - The maximum request size when sending a message is 8MiB.
     * - For the embed object, you can set every field except type (it will be rich regardless of if you try to set
     *   it), provider, video, and any height, width, or proxy_url values for images.
     * - Examples for file uploads are available in Uploading Files.
     * - Files must be attached using a multipart/form-data body as described in Uploading Files.
     * - Note that when sending a message, you must provide a value for at least one of content, embeds, or files[n].
     *
     * Discord may strip certain characters from message content, like invalid unicode characters or characters which
     * cause unexpected message formatting. If you are passing user-generated strings into message content, consider
     * sanitizing the data to prevent unexpected behavior and utilizing allowed_mentions to prevent unexpected
     * mentions.
     *
     * This endpoint supports the X-Audit-Log-Reason header.
     *
     * @param  mixed $channelID
     * @param  mixed $params
     * @return void
     */
    public function startThreadInForumChannel($channelID, $params)
    {
        $url = \sprintf('%s/channels/%s/threads', $this->apiUrl, $channelID);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Adds the current user to a thread. Also requires the thread is not archived. Returns a 204 empty response on
     * success. Fires a Thread Members Update Gateway event.
     *
     * @param  integer $channelID
     * @return array
     */
    public function joinThread($channelID)
    {
        $url = \sprintf('%s/channels/%s/thread-members/@me', $this->apiUrl, $channelID);

        return $this->execute($url, 'PUT');
    }

    /**
     * Adds another member to a thread. Requires the ability to send messages in the thread. Also requires the thread
     * is not archived. Returns a 204 empty response on success. Fires a Thread Members Update Gateway event.
     *
     * @param  integer $channelID
     * @param  integer $userID
     * @return array
     */
    public function addThreadMember($channelID, $userID)
    {
        $url = \sprintf('%s/channels/%s/thread-members/%s', $this->apiUrl, $channelID, $userID);

        return $this->execute($url, 'PUT');
    }

    /**
     * Removes the current user from a thread. Also requires the thread is not archived. Returns a 204 empty response
     * on success. Fires a Thread Members Update Gateway event.
     *
     * @param  integer $channelID
     * @return array
     */
    public function leaveThread($channelID)
    {
        $url = \sprintf('%s/channels/%s/thread-members/@me', $this->apiUrl, $channelID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Removes another member from a thread. Requires the MANAGE_THREADS permission, or the creator of the thread if it
     * is a GUILD_PRIVATE_THREAD. Also requires the thread is not archived. Returns a 204 empty response on success.
     * Fires a Thread Members Update Gateway event.
     *
     * @param  integer $channelID
     * @param  integer $userID
     * @return array
     */
    public function removeThreadMember($channelID, $userID)
    {
        $url = \sprintf('%s/channels/%s/thread-members/%s', $this->apiUrl, $channelID, $userID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Returns a thread member object for the specified user if they are a member of the thread, returns a 404 response
     * otherwise.
     *
     * @param  int $channelID
     * @param  int $userID
     * @return array
     */
    public function getThreadMember($channelID, $userID)
    {
        $url = \sprintf('%s/channels/%s/thread-members/%s', $this->apiUrl, $channelID, $userID);

        return $this->execute($url, 'GET');
    }

    /**
     * Returns array of thread members objects that are members of the thread.
     *
     * This endpoint is restricted according to whether the GUILD_MEMBERS Privileged Intent is enabled for your
     * application.
     *
     * @param  integer $channelID
     * @return array
     */
    public function listThreadMembers($channelID)
    {
        $url = \sprintf('%s/channels/%s/thread-members', $this->apiUrl, $channelID);

        return $this->execute($url, 'GET');
    }

    /**
     * Returns archived threads in the channel that are public. When called on a GUILD_TEXT channel, returns threads of
     * type GUILD_PUBLIC_THREAD. When called on a GUILD_NEWS channel returns threads of type GUILD_NEWS_THREAD. Threads
     * are ordered by archive_timestamp, in descending order. Requires the READ_MESSAGE_HISTORY permission.
     *
     * @param  integer $channelID
     * @param  array $params
     * @return array
     */
    public function listPublicArchivedThreads($channelID, $params = [])
    {
        $url = \sprintf('%s/channels/%s/threads/archived/public', $this->apiUrl, $channelID);
        if (!empty($params)) {
            $url .= '?' . \http_build_query($params, '', '&');
        }

        return $this->execute($url, 'GET');
    }

    /**
     * Returns archived threads in the channel that are of type GUILD_PRIVATE_THREAD. Threads are ordered by
     * archive_timestamp, in descending order. Requires both the READ_MESSAGE_HISTORY and MANAGE_THREADS permissions.
     *
     * @param  integer $channelID
     * @param  array $params
     * @return array
     */
    public function listPrivateArchivedThreads($channelID, $params = [])
    {
        $url = \sprintf('%s/channels/%s/threads/archived/private', $this->apiUrl, $channelID);
        if (!empty($params)) {
            $url .= '?' . \http_build_query($params, '', '&');
        }

        return $this->execute($url, 'GET');
    }

    /**
     * Returns archived threads in the channel that are of type GUILD_PRIVATE_THREAD, and the user has joined. Threads
     * are ordered by their id, in descending order. Requires the READ_MESSAGE_HISTORY permission.
     *
     * @param  integer $channelID
     * @param  array $params
     * @return array
     */
    public function listJoinedPrivateArchivedThreads($channelID, $params = [])
    {
        $url = \sprintf('%s/channels/%s/users/@me/threads/archived/private', $this->apiUrl, $channelID);
        if (!empty($params)) {
            $url .= '?' . \http_build_query($params, '', '&');
        }

        return $this->execute($url, 'GET');
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
    public function listGuildEmojis()
    {
        $url = \sprintf('%s/guilds/%s/emojis', $this->apiUrl, $this->guildID);

        return $this->execute($url);
    }

    /**
     * Returns an emoji object for the given guild and emoji IDs
     *
     * @param   integer $emojiID    ID des Emojis
     * @return array
     */
    public function getGuildEmoji($emojiID)
    {
        $url = \sprintf('%s/guilds/%s/emojis/%s', $this->apiUrl, $this->guildID, $emojiID);

        return $this->execute($url);
    }

    /**
     * Create a new emoji for the guild.
     * Requires the MANAGE_EMOJIS permission.
     * Returns the new emoji object on success.
     * Fires a Guild Emojis Update Gateway event.
     * Emojis and animated emojis have a maximum file size of 256kb.
     * Attempting to upload an emoji larger than this limit will fail and return 400 Bad Request and an error message,
     * but not a JSON status code.
     *
     * @param   string  $name   Name des Emojis
     * @param   string  $image  Bild als base64 code
     * @param   array   $roles  Gruppen die diesen Emoji nutzen dürfen
     * @return  array
     */
    public function createGuildEmoji($name, $image, $roles = [])
    {
        $url = \sprintf('%s/guilds/%s/emojis', $this->apiUrl, $this->guildID);
        $params = [
            'name' => $name,
            'image' => $image,
        ];
        if (\count($roles) > 0) {
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
    public function modifyGuildEmoji($emojiID, array $params)
    {
        $url = \sprintf('%s/guilds/%s/emojis/%s', $this->apiUrl, $this->guildID, $emojiID);

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
    public function deleteGuildEmoji($emojiID)
    {
        $url = \sprintf('%s/guilds/%s/emojis/%s', $this->apiUrl, $this->guildID, $emojiID);

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
    public function createGuild($params)
    {
        $url = \sprintf('%s/guilds', $this->apiUrl);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Returns the guild object for the given id.
     *
     * @return  array
     */
    public function getGuild(bool $withCounts = false)
    {
        $url = \sprintf('%s/guilds/%s', $this->apiUrl, $this->guildID);
        if ($withCounts) {
            $url .= '?with_counts=1';
        }

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
    public function modifyGuild($params)
    {
        $url = \sprintf('%s/guilds/%s', $this->apiUrl, $this->guildID);

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
    public function deleteGuild()
    {
        $url = \sprintf('%s/guilds/%s', $this->apiUrl, $this->guildID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Returns a list of guild channel objects.
     *
     * @return  array
     */
    public function getGuildChannels()
    {
        $url = \sprintf('%s/guilds/%s/channels', $this->apiUrl, $this->guildID);

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
    public function createGuildChannel($params)
    {
        $url = \sprintf('%s/guilds/%s/channels', $this->apiUrl, $this->guildID);

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
    public function modifyGuildChannelPositions($channelID, $position)
    {
        $url = \sprintf('%s/guilds/%s/channels', $this->apiUrl, $this->guildID);
        $params = [
            'id' => $channelID,
            'position' => $position,
        ];

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Returns a guild member object for the specified user.
     *
     * @param   integer     $userID     ID des Benutzer
     * @return  array
     */
    public function getGuildMember($userID)
    {
        $url = \sprintf('%s/guilds/%s/members/%s', $this->apiUrl, $this->guildID, $userID);

        return $this->execute($url);
    }

    /**
     * Returns a list of guild member objects that are members of the guild.
     *
     * @param   array   $params     Parameter
     * @return  array
     */
    public function listGuildMembers($params = [])
    {
        $url = \sprintf('%s/guilds/%s/members', $this->apiUrl, $this->guildID);
        if (!empty($params)) {
            $url .= '?' . \http_build_query($params, '', '&');
        }

        return $this->execute($url);
    }

    /**
     * Adds a user to the guild, provided you have a valid oauth2 access token for the user with the guilds.join scope.
     * Returns a 201 Created with the guild member as the body, or 204 No Content if the user is already a member of
     * the guild.
     * Fires a Guild Member Add Gateway event.
     * Requires the bot to have the CREATE_INSTANT_INVITE permission.
     * All parameters to this endpoint except for access_token are optional.
     *
     * @param   integer     $userID         ID des Benutzers
     * @param   string      $accessToken    Access-Token des Benutzer
     * @param   array       $params         Zusätzliche optionale Parameter
     * @return  array
     */
    public function addGuildMember(
        $userID,
        #[SensitiveParameter]
        $accessToken,
        $params = []
    ) {
        $url = \sprintf('%s/guilds/%s/members/%s', $this->apiUrl, $this->guildID, $userID);
        $params = \array_merge([
            'access_token' => $accessToken,
        ], $params);

        return $this->execute($url, 'PUT', $params, 'application/json');
    }

    /**
     * Modify attributes of a guild member.
     * Returns a 204 empty response on success.
     * Fires a Guild Member Update Gateway event.
     * All parameters to this endpoint are optional.
     * When moving members to channels, the API user must have permissions to both connect to the channel and have the
     * MOVE_MEMBERS permission.
     *
     * @param   integer $userID     ID des Benutzers
     * @param   array   $params     JSON-Parameter
     * @return  array
     */
    public function modifyGuildMember($userID, $params)
    {
        $url = \sprintf('%s/guilds/%s/members/%s', $this->apiUrl, $this->guildID, $userID);

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
    public function modifyCurrentUserNick($nick)
    {
        $url = \sprintf('%s/guilds/%s/members/@me/nick', $this->apiUrl, $this->guildID);
        $params = [
            'nick' => $nick,
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
    public function addGuildMemberRole($userID, $roleID)
    {
        $url = \sprintf('%s/guilds/%s/members/%s/roles/%s', $this->apiUrl, $this->guildID, $userID, $roleID);

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
    public function removeGuildMemberRole($userID, $roleID)
    {
        $url = \sprintf('%s/guilds/%s/members/%s/roles/%s', $this->apiUrl, $this->guildID, $userID, $roleID);

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
    public function removeGuildMember($userID)
    {
        $url = \sprintf('%s/guilds/%s/members/%s', $this->apiUrl, $this->guildID, $userID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Returns a list of ban objects for the users banned from this guild.
     * Requires the BAN_MEMBERS permission.
     *
     * @return array
     */
    public function getGuildBans()
    {
        $url = \sprintf('%s/guilds/%s/bans', $this->apiUrl, $this->guildID);

        return $this->execute($url);
    }

    /**
     * Returns a ban object for the given user or a 404 not found if the ban cannot be found.
     * Requires the BAN_MEMBERS permission.
     *
     * @param   integer     $userID     ID des Benutzer
     * @return  array
     */
    public function getGuildBan($userID)
    {
        $url = \sprintf('%s/guilds/%s/bans/%s', $this->apiUrl, $this->guildID, $userID);

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
    public function createGuildBan($userID, $params = [])
    {
        $url = \sprintf('%s/guilds/%s/bans/%s', $this->apiUrl, $this->guildID, $userID);

        return $this->execute($url, 'PUT', $params, 'application/json');
    }

    /**
     * Remove the ban for a user. Requires the BAN_MEMBERS permissions. Returns a 204 empty response on success. Fires
     * a Guild Ban Remove Gateway event.
     *
     * @param   integer     $userID     ID des Benutzer
     * @return  array
     */
    public function removeGuildBan($userID)
    {
        $url = \sprintf('%s/guilds/%s/bans/%s', $this->apiUrl, $this->guildID, $userID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Returns a list of role objects for the guild.
     *
     * @return array
     */
    public function getGuildRoles()
    {
        $url = \sprintf('%s/guilds/%s/roles', $this->apiUrl, $this->guildID);

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
    public function createGuildRole($params = [])
    {
        $url = \sprintf('%s/guilds/%s/roles', $this->apiUrl, $this->guildID);

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
    public function modifyGuildRolePosition($roleID, $position)
    {
        $url = \sprintf('%s/guilds/%s/roles', $this->apiUrl, $this->guildID);
        $params = [
            'id' => $roleID,
            'position' => $position,
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
    public function modifyGuildRole($roleID, $params = [])
    {
        $url = \sprintf('%s/guilds/%s/roles/%s', $this->apiUrl, $this->guildID, $roleID);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Modify a guild's MFA level. Requires guild ownership. Returns the updated level on success. Fires a Guild Update
     * Gateway event.
     *
     * This endpoint supports the X-Audit-Log-Reason header.
     *
     * @param  array $params
     * @return array
     */
    public function modifyGuildMfaLevel($params)
    {
        $url = \sprintf('%s/guilds/%s/mfa', $this->apiUrl, $this->guildID);

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
    public function deleteGuildRole($roleID)
    {
        $url = \sprintf('%s/guilds/%s/roles/%s', $this->apiUrl, $this->guildID, $roleID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Returns an object with one 'pruned' key indicating the number of members that would be removed in a prune
     * operation.
     * Requires the KICK_MEMBERS permission.
     *
     * @param   integer     $days       number of days to count prune for (1 or more)
     * @return  array
     */
    public function getGuildPruneCount($days = 1)
    {
        $url = \sprintf('%s/guilds/%s/prune', $this->apiUrl, $this->guildID);
        $url .= '?' . \http_build_query([
            'days' => $days,
        ], '', '&');

        return $this->execute($url);
    }

    /**
     * Begin a prune operation.
     * Requires the KICK_MEMBERS permission.
     * Returns an object with one 'pruned' key indicating the number of members that were removed in the prune
     * operation.
     * For large guilds it's recommended to set the compute_prune_count option to false, forcing 'pruned' to null.
     * Fires multiple Guild Member Remove Gateway events.
     *
     * @param   integer     $days               number of days to count prune for (1 or more)
     * @param   boolean     $computePruneCount  whether 'pruned' is returned, discouraged for large guilds
     * @return  array
     */
    public function beginGuildPrune($days, $computePruneCount = false)
    {
        $url = \sprintf('%s/guilds/%s/prune', $this->apiUrl, $this->guildID);
        $params = [
            'days' => $days,
            'compute_prune_count' => $computePruneCount,
        ];

        return $this->execute($url, 'POST', $params);
    }

    /**
     * Returns a list of voice region objects for the guild.
     * Unlike the similar /voice route, this returns VIP servers when the guild is VIP-enabled.
     *
     * @return  array
     */
    public function getGuildVoiceRegions()
    {
        $url = \sprintf('%s/guilds/%s/regions', $this->apiUrl, $this->guildID);

        return $this->execute($url);
    }

    /**
     * Returns a list of invite objects (with invite metadata) for the guild.
     * Requires the MANAGE_GUILD permission.
     *
     * @return  array
     */
    public function getGuildInvites()
    {
        $url = \sprintf('%s/guilds/%s/invites', $this->apiUrl, $this->guildID);

        return $this->execute($url);
    }

    /**
     * Returns a list of integration objects for the guild.
     * Requires the MANAGE_GUILD permission.
     *
     * @return  array
     */
    public function getGuildIntegrations()
    {
        $url = \sprintf('%s/guilds/%s/integrations', $this->apiUrl, $this->guildID);

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
    public function createGuildIntegration($type, $id)
    {
        $url = \sprintf('%s/guilds/%s/integrations', $this->apiUrl, $this->guildID);
        $params = [
            'type' => $type,
            'id' => $id,
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
    public function modifyGuildIntegration($integrationID, $params)
    {
        $url = \sprintf('%s/guilds/%s/integrations/%s', $this->apiUrl, $this->guildID, $integrationID);

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
    public function deleteGuildIntegration($integrationID)
    {
        $url = \sprintf('%s/guilds/%s/integrations/%s', $this->apiUrl, $this->guildID, $integrationID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Returns a guild widget object. Requires the MANAGE_GUILD permission.
     *
     * @return array
     */
    public function getGuildWidgetSettings()
    {
        $url = \sprintf('%s/guilds/%s/widget', $this->apiUrl, $this->guildID);

        return $this->execute($url, 'GET');
    }

    /**
     * Modify a guild widget object for the guild. All attributes may be passed in with JSON and modified. Requires the
     * MANAGE_GUILD permission. Returns the updated guild widget object.
     *
     * @param  array $params
     * @return array
     */
    public function modifyGuildWidget($params)
    {
        $url = \sprintf('%s/guilds/%s/widget', $this->apiUrl, $this->guildID);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Returns the widget for the guild.
     *
     * @return array
     */
    public function getGuildWidget()
    {
        $url = \sprintf('%s/guilds/%s/widget.json', $this->apiUrl, $this->guildID);

        return $this->execute($url, 'GET');
    }

    /**
     * Returns a partial invite object for guilds with that feature enabled.
     * Requires the MANAGE_GUILD permission.
     * code will be null if a vanity url for the guild is not set.
     *
     * @return array
     */
    public function getGuildVanityUrl()
    {
        $url = \sprintf('%s/guilds/%s/vanity-url', $this->apiUrl, $this->guildID);

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
    public function getGuildWidgetImage($style = 'shield')
    {
        $url = \sprintf('%s/guilds/%s/widget.png?style=%s', $this->apiUrl, $this->guildID, $style);

        return $this->execute($url);
    }

    /**
     * Returns the Welcome Screen object for the guild.
     *
     * @return array
     */
    public function getGuildWelcomeScreen()
    {
        $url = \sprintf('%s/guilds/%s/welcome-screen', $this->apiUrl, $this->guildID);

        return $this->execute($url, 'GET');
    }

    /**
     * Modify the guild's Welcome Screen. Requires the MANAGE_GUILD permission. Returns the updated Welcome Screen
     * object.
     *
     * All parameters to this endpoint are optional and nullable
     *
     * @param  array $params
     * @return array
     */
    public function modifyGuildWelcomeScreen($params)
    {
        $url = \sprintf('%s/guilds/%s/welcome-screen', $this->apiUrl, $this->guildID);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Updates the current user's voice state.
     *
     * @param  array $params
     * @return array
     */
    public function modifyCurrentUserVoiceState($params)
    {
        $url = \sprintf('%s/guilds/%s/voice-states/@me', $this->apiUrl, $this->guildID);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Updates another user's voice state.
     *
     * @param  integer $userID
     * @param  array $params
     * @return array
     */
    public function modifyUserVoiceState($userID, $params)
    {
        $url = \sprintf('%s/guilds/%s/voice-states/%s', $this->apiUrl, $this->guildID, $userID);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /////////////////////////////////////
    // Guild End
    /////////////////////////////////////

    /////////////////////////////////////
    // Guild Scheduled Event Start
    /////////////////////////////////////

    /**
     * Returns a list of guild scheduled event objects for the given guild.
     *
     * @param  integer $guildID
     * @return array
     */
    public function listScheduledEventsForGuild($guildID, $withUserCount = false)
    {
        $url = \sprintf('%s/guilds/%s/scheduled-events', $this->apiUrl, $guildID);
        if ($withUserCount) {
            $url .= '?with_user_count=true';
        }

        return $this->execute($url);
    }

    /**
     * Create a guild scheduled event in the guild. Returns a guild scheduled event object on success.
     *
     * A guild can have a maximum of 100 events with SCHEDULED or ACTIVE status at any time.
     *
     * @param  integer $guildID
     * @param  array $params
     * @return array
     */
    public function createGuildScheduledEvent($guildID, $params)
    {
        $url = \sprintf('%s/guilds/%s/scheduled-events', $this->apiUrl, $guildID);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Get a guild scheduled event. Returns a guild scheduled event object on success.
     *
     * @param  integer $guildID
     * @param  integer $scheduledEventID
     * @return array
     */
    public function getGuildScheduledEvent($guildID, $scheduledEventID, $withUserCount = false)
    {
        $url = \sprintf('%s/guilds/%s/scheduled-events/%s', $this->apiUrl, $guildID, $scheduledEventID);
        if ($withUserCount) {
            $url .= '?with_user_count=true';
        }

        return $this->execute($url, 'POST');
    }

    /**
     * Modify a guild scheduled event. Returns the modified guild scheduled event object on success.
     *
     * To start or end an event, use this endpoint to modify the event's status field.
     *
     * @param  integer $guildID
     * @param  integer $scheduledEventID
     * @param  array $params
     * @return array
     */
    public function modifyGuildScheduledEvent($guildID, $scheduledEventID, $params)
    {
        $url = \sprintf('%s/guilds/%s/scheduled-events/%s', $this->apiUrl, $guildID, $scheduledEventID);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Delete a guild scheduled event. Returns a 204 on success.
     *
     * @param  integer $guildID
     * @param  integer $scheduledEventID
     * @return array
     */
    public function deleteGuildScheduledEvent($guildID, $scheduledEventID)
    {
        $url = \sprintf('%s/guilds/%s/scheduled-events/%s', $this->apiUrl, $guildID, $scheduledEventID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Get a list of guild scheduled event users subscribed to a guild scheduled event. Returns a list of guild
     * scheduled event user objects on success. Guild member data, if it exists, is included if the with_member query
     * parameter is set.
     *
     * @param  integer $guildID
     * @param  integer $scheduledEventID
     * @param  array $queryParams
     * @return array
     */
    public function getGuildScheduledEventUsers($guildID, $scheduledEventID, array $queryParams = [])
    {
        $url = \sprintf('%s/guilds/%s/scheduled-events/%s/users', $this->apiUrl, $guildID, $scheduledEventID);
        if (!empty($queryParams)) {
            $url .= '?' . \http_build_query($queryParams, '', '&');
        }

        return $this->execute($url);
    }

    /////////////////////////////////////
    // Guild Scheduled Event End
    /////////////////////////////////////

    /////////////////////////////////////
    // Guild Template Start
    /////////////////////////////////////

    /**
     * Returns a guild template object for the given code.
     *
     * @param  string $templateCode
     * @return array
     */
    public function getGuildTemplate($templateCode)
    {
        $url = \sprintf('%s/guilds/templates/%s', $this->apiUrl, $templateCode);

        return $this->execute($url, 'GET');
    }

    /**
     * Create a new guild based on a template. Returns a guild object on success. Fires a Guild Create Gateway event.
     *
     * This endpoint can be used only by bots in less than 10 guilds.
     *
     * @param  string $templateCode
     * @param  array $params
     * @return array
     */
    public function createGuildFromGuildTemplate($templateCode, $params)
    {
        $url = \sprintf('%s/guilds/templates/%s', $this->apiUrl, $templateCode);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Returns an array of guild template objects. Requires the MANAGE_GUILD permission.
     *
     * @array void
     */
    public function getGuildTemplates()
    {
        $url = \sprintf('%s/guilds/%s/templates', $this->apiUrl, $this->guildID);

        return $this->execute($url, 'GET');
    }

    /**
     * Creates a template for the guild. Requires the MANAGE_GUILD permission. Returns the created guild template
     * object on success.
     *
     * @param  array $params
     * @return array
     */
    public function createGuildTemplate($params)
    {
        $url = \sprintf('%s/guilds/%s/templates', $this->apiUrl, $this->guildID);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Syncs the template to the guild's current state. Requires the MANAGE_GUILD permission. Returns the guild
     * template object on success.
     *
     * @param  string $templateCode
     * @return array
     */
    public function syncGuildTemplate($templateCode)
    {
        $url = \sprintf('%s/guilds/templates/%s', $this->apiUrl, $templateCode);

        return $this->execute($url, 'PUT');
    }

    /**
     * Modifies the template's metadata. Requires the MANAGE_GUILD permission. Returns the guild template object on
     * success.
     *
     * @param  string $templateCode
     * @param  array $params
     * @return array
     */
    public function modifyGuildTemplate($templateCode, $params)
    {
        $url = \sprintf('%s/guilds/templates/%s', $this->apiUrl, $templateCode);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Deletes the template. Requires the MANAGE_GUILD permission. Returns the deleted guild template object on success.
     *
     * @param  string $templateCode
     * @return array
     */
    public function deleteGuildTemplate($templateCode)
    {
        $url = \sprintf('%s/guilds/templates/%s', $this->apiUrl, $templateCode);

        return $this->execute($url, 'DELETE');
    }

    /////////////////////////////////////
    // Guild Template End
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
    public function getInvite($inviteCode, $withCounts = false)
    {
        $url = \sprintf('%s/invites/%s', $this->apiUrl, $inviteCode);
        if ($withCounts) {
            $url .= '?with_counts=1';
        }

        return $this->execute($url);
    }

    /**
     * Delete an invite.
     * Requires the MANAGE_CHANNELS permission on the channel this invite belongs to, or MANAGE_GUILD to remove any
     * invite across the guild.
     * Returns an invite object on success.
     *
     * @param   string  $inviteCode EinladungsCode
     * @return  array
     */
    public function deleteInvite($inviteCode)
    {
        $url = \sprintf('%s/invites/%s', $this->apiUrl, $inviteCode);

        return $this->execute($url, 'DELETE');
    }

    /////////////////////////////////////
    // Invite End
    /////////////////////////////////////

    /////////////////////////////////////
    // Stage Instance Start
    /////////////////////////////////////

    /**
     * Creates a new Stage instance associated to a Stage channel.
     *
     * Requires the user to be a moderator of the Stage channel.
     *
     * @param  array $params
     * @return array
     */
    public function createStageInstance($params)
    {
        $url = \sprintf('%s/stage-instances', $this->apiUrl);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Gets the stage instance associated with the Stage channel, if it exists.
     *
     * @param  integer $channelID
     * @return array
     */
    public function getStageInstance($channelID)
    {
        $url = \sprintf('%s/stage-instances/%s', $this->apiUrl, $channelID);

        return $this->execute($url, 'GET');
    }

    /**
     * Updates fields of an existing Stage instance.
     *
     * Requires the user to be a moderator of the Stage channel.
     *
     * @param  integer $channelID
     * @param  array $params
     * @return array
     */
    public function modifyStageInstance($channelID, $params)
    {
        $url = \sprintf('%s/stage-instances/%s', $this->apiUrl, $channelID);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Deletes the Stage instance.
     *
     * Requires the user to be a moderator of the Stage channel.
     *
     * @param  integer $channelID
     * @return array
     */
    public function deleteStageInstance($channelID)
    {
        $url = \sprintf('%s/stage-instances/%s', $this->apiUrl, $channelID);

        return $this->execute($url, 'DELETE');
    }

    /////////////////////////////////////
    // Stage Instance End
    /////////////////////////////////////

    /////////////////////////////////////
    // Sticker Start
    /////////////////////////////////////

    /**
     * Returns a sticker object for the given sticker ID.
     *
     * @param  integer $stickerID
     * @return array
     */
    public function getSticker($stickerID)
    {
        $url = \sprintf('%s/stickers/%s', $this->apiUrl, $stickerID);

        return $this->execute($url);
    }

    /**
     * Returns the list of sticker packs available to Nitro subscribers.
     *
     * @return array
     */
    public function listNitroStickerPacks()
    {
        $url = \sprintf('%s/sticker-packs', $this->apiUrl);

        return $this->execute($url);
    }

    /**
     * Returns an array of sticker objects for the given guild. Includes user fields if the bot has the
     * MANAGE_EMOJIS_AND_STICKERS permission.
     *
     * @param  integer $guildID
     * @return array
     */
    public function listGuildStickers($guildID)
    {
        $url = \sprintf('%s/guilds/%s/stickers', $this->apiUrl, $guildID);

        return $this->execute($url);
    }

    /**
     * Returns a sticker object for the given guild and sticker IDs. Includes the user field if the bot has the
     * MANAGE_EMOJIS_AND_STICKERS permission.
     *
     * @param  integer $guildID
     * @param  integer $stickerID
     * @return array
     */
    public function getGuildSticker($guildID, $stickerID)
    {
        $url = \sprintf('%s/guilds/%s/stickers/%s', $this->apiUrl, $guildID, $stickerID);

        return $this->execute($url);
    }

    /**
     * Modify the given sticker. Requires the MANAGE_EMOJIS_AND_STICKERS permission. Returns the updated sticker object
     * on success.
     *
     * @param  integer $guildID
     * @param  integer $stickerID
     * @param  array $params
     * @return array
     */
    public function modifyGuildSticker($guildID, $stickerID, $params)
    {
        $url = \sprintf('%s/guilds/%s/stickers/%s', $this->apiUrl, $guildID, $stickerID);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Delete the given sticker. Requires the MANAGE_EMOJIS_AND_STICKERS permission. Returns 204 No Content on success.
     *
     * @param  integer $guildID
     * @param  integer $stickerID
     * @return array
     */
    public function deleteGuildSticker($guildID, $stickerID)
    {
        $url = \sprintf('%s/guilds/%s/stickers/%s', $this->apiUrl, $guildID, $stickerID);

        return $this->execute($url, 'DELETE');
    }

    /////////////////////////////////////
    // Sticker End
    /////////////////////////////////////

    /////////////////////////////////////
    // User Start
    /////////////////////////////////////

    /**
     * Returns the user object of the requester's account.
     * For OAuth2, this requires the identify scope, which will return the object without an email, and optionally the
     * email scope, which returns the object with an email.
     *
     * @return array
     */
    public function getCurrentUser()
    {
        $url = \sprintf('%s/users/@me', $this->apiUrl);

        return $this->execute($url);
    }

    /**
     * Returns a user object for a given user ID.
     *
     * @param   integer $userID ID des Benutzers
     * @return  array
     */
    public function getUser($userID)
    {
        $url = \sprintf('%s/users/%s', $this->apiUrl, $userID);

        return $this->execute($url);
    }

    /**
     * Modify the requester's user account settings. Returns a user object on success.
     *
     * @param   array   $params Parameter
     * @return  array
     */
    public function modifyCurrentUser($params)
    {
        $url = \sprintf('%s/users/@me', $this->apiUrl);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Returns a list of partial guild objects the current user is a member of.
     * Requires the guilds OAuth2 scope.
     *
     * @return array
     */
    public function getCurrentUserGuilds($params = [])
    {
        $url = \sprintf('%s/users/@me/guilds', $this->apiUrl);
        if (!empty($params)) {
            $url .= '?' . \http_build_query($params, '', '&');
        }

        return $this->execute($url);
    }

    /**
     * Leave a guild.
     * Returns a 204 empty response on success.
     *
     * @return array
     */
    public function leaveGuild()
    {
        $url = \sprintf('%s/users/@me/guilds/%s', $this->apiUrl, $this->guildID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Returns a list of DM channel objects.
     * For bots, this is no longer a supported method of getting recent DMs, and will return an empty array.
     *
     * @return array
     */
    public function getUserDMs()
    {
        $url = \sprintf('%s/users/@me/channels', $this->apiUrl);

        return $this->execute($url);
    }

    /**
     * Create a new DM channel with a user.
     * Returns a DM channel object.
     *
     * @param   integer $recipientID    ID des Empfängers
     * @return  array
     */
    public function createDM($recipientID)
    {
        $url = \sprintf('%s/users/@me/channels', $this->apiUrl);
        $params = [
            'recipient_id' => $recipientID,
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
    public function createGroupDM($params)
    {
        $url = \sprintf('%s/users/@me/channels', $this->apiUrl);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Returns a list of connection objects.
     * Requires the connections OAuth2 scope.
     *
     * @return  array
     */
    public function getUserConnections()
    {
        $url = \sprintf('%s/users/@me/connections', $this->apiUrl);

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
    public function listVoiceRegions()
    {
        $url = \sprintf('%s/voice/regions', $this->apiUrl);

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
    public function createWebhook($channelID, $name, $avatar = null)
    {
        $url = \sprintf('%s/channels/%s/webhooks', $this->apiUrl, $channelID);
        $params = [
            'name' => $name,
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
    public function getChannelWebhooks($channelID)
    {
        $url = \sprintf('%s/channels/%s/webhooks', $this->apiUrl, $channelID);

        return $this->execute($url);
    }

    /**
     * Returns a list of guild webhook objects.
     * Requires the MANAGE_WEBHOOKS permission.
     *
     * @return array
     */
    public function getGuildWebhooks()
    {
        $url = \sprintf('%s/guilds/%s/webhooks', $this->apiUrl, $this->guildID);

        return $this->execute($url);
    }

    /**
     * Returns the new webhook object for the given id.
     *
     * @param   integer $webhookID  ID des Webhooks
     * @return  array
     */
    public function getWebhook($webhookID)
    {
        $url = \sprintf('%s/webhooks/%s', $this->apiUrl, $webhookID);

        return $this->execute($url);
    }

    /**
     * Same as above, except this call does not require authentication and returns no user in the webhook object.
     *
     * @param   integer $webhookID      ID des Webhooks
     * @param   string  $webhookToken   Token des Webhooks
     * @return  array
     */
    public function getWebhookWithToken(
        $webhookID,
        #[SensitiveParameter]
        $webhookToken
    ) {
        $url = \sprintf('%s/webhooks/%s/%s', $this->apiUrl, $webhookID, $webhookToken);

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
    public function modifyWebhook($webhookID, $params)
    {
        $url = \sprintf('%s/webhooks/%s', $this->apiUrl, $webhookID);

        return $this->execute($url, 'PATCH', $params, 'application/json');
    }

    /**
     * Same as above, except this call does not require authentication, does not accept a channel_id parameter in the
     * body, and does not return a user in the webhook object.
     *
     * @param   integer $webhookID      ID des Webhooks
     * @param   string  $webhookToken   Token des Webhooks
     * @param   array   $params         Parameter
     * @return  array
     */
    public function modifyWebhookWithToken(
        $webhookID,
        #[SensitiveParameter]
        $webhookToken,
        $params
    ) {
        $url = \sprintf('%s/webhooks/%s/%s', $this->apiUrl, $webhookID, $webhookToken);

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
    public function deleteWebhook($webhookID)
    {
        $url = \sprintf('%s/webhooks/%s', $this->apiUrl, $webhookID);

        return $this->execute($url, 'DELETE');
    }

    /**
     * Same as above, except this call does not require authentication.
     *
     * @param   integer $webhookID      ID des Webhooks
     * @param   string  $webhookToken   Token des Webhooks
     * @return  array
     */
    public function deleteWebhookWithToken(
        $webhookID,
        #[SensitiveParameter]
        $webhookToken
    ) {
        $url = \sprintf('%s/webhooks/%s/%s', $this->apiUrl, $webhookID, $webhookToken);

        return $this->execute($url, 'DELETE');
    }

    /**
     * executes a webhook
     *
     * @param   integer $webhookID      ID des Webhooks
     * @param   string  $webhookToken   Token des Webhooks
     * @param   array   $params         Parameter
     * @param   boolean $wait           waits for server confirmation of message send before response, and returns the
     *                                  created message body (defaults to false; when false a message that is not saved
     *                                  does not return an error)
     * @return  array
     */
    public function executeWebhook(
        $webhookID,
        #[SensitiveParameter]
        $webhookToken,
        $params,
        $wait = false,
        $threadID = null
    ) {
        $url = new Uri(
            \sprintf('%s/webhooks/%s/%s', $this->apiUrl, $webhookID, $webhookToken)
        );
        $queryParams = [];
        if ($wait) {
            $queryParams['wait'] = 'true';
        }
        if (!empty($threadID)) {
            $queryParams['thread_id'] = $threadID;
        }

        if ($queryParams !== []) {
            $encodedParameters = \http_build_query($queryParams, '', '&');
            $url = $url->withQuery($encodedParameters);
        }

        $contentType = 'application/json';
        if (isset($params['file'])) {
            $contentType = 'multipart/form-data';
        }

        return $this->execute((string)$url, 'POST', $params, $contentType);
    }

    /**
     * Refer to Slack's documentation for more information. We do not support Slack's channel, icon_emoji, mrkdwn, or
     * mrkdwn_in properties.
     *
     * @param   integer $webhookID      ID des Webhooks
     * @param   string  $webhookToken   Token des Webhooks
     * @param   array   $params         Parameter
     * @param   boolean $wait           waits for server confirmation of message send before response, and returns the
     *                                  created message body (defaults to false; when false a message that is not saved
     *                                  does not return an error)
     * @return  array
     */
    public function executeSlackCompatibleWebhook(
        $webhookID,
        #[SensitiveParameter]
        $webhookToken,
        $params,
        $wait = false
    ) {
        $url = \sprintf('%s/webhooks/%s/%s/slack', $this->apiUrl, $webhookID, $webhookToken);

        return $this->execute($url, 'POST', $params, 'application/json');
    }

    /**
     * Add a new webhook to your GitHub repo (in the repo's settings), and use this endpoint as the "Payload URL." You
     * can choose what events your Discord channel receives by choosing the "Let me select individual events" option
     * and selecting individual events for the new webhook you're configuring.
     *
     * @param   integer $webhookID      ID des Webhooks
     * @param   string  $webhookToken   Token des Webhooks
     * @param   array   $params         Parameter
     * @param   boolean $wait           waits for server confirmation of message send before response, and returns the
     *                                  created message body (defaults to false; when false a message that is not saved
     *                                  does not return an error)
     * @return  array
     */
    public function executeGithubCompatibleWebhook(
        $webhookID,
        #[SensitiveParameter]
        $webhookToken,
        $params,
        $wait = false
    ) {
        $url = \sprintf('%s/webhooks/%s/%s/github', $this->apiUrl, $webhookID, $webhookToken);

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
    public function oauth2Authorize($clientID, $scope, $redirectUri, $state = null)
    {
        $url = \sprintf('%s/oauth2/authorize?response_type=code&client_id=%s&', $this->apiUrl, $clientID);
        $params = [
            'scope' => \implode(' ', $scope),
            'redirect_uri' => $redirectUri,
        ];
        if ($state !== null) {
            $params['state'] = $state;
        }
        $url .= \http_build_query($params, '', '&');

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
    public function oauth2Token(
        $clientID,
        #[SensitiveParameter]
        $clientSecret,
        $code,
        $redirectUri,
        $grantType = 'authorization_code'
    ) {
        $url = \sprintf('%s/oauth2/token', $this->apiUrl);
        $params = [
            'client_id' => $clientID,
            'client_secret' => $clientSecret,
            'grant_type' => $grantType,
            'code' => $code,
            'redirect_uri' => $redirectUri,
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
    public function getCurrentApplicationInformation()
    {
        $url = \sprintf('%s/oauth2/applications/@me', $this->apiUrl);

        return $this->execute($url);
    }

    /////////////////////////////////////
    // OAuth2 End
    /////////////////////////////////////

    /////////////////////////////////////
    // Gateway Start
    /////////////////////////////////////

    /**
     * Returns an object with a single valid WSS URL, which the client can use for Connecting. Clients should cache
     * this value and only call this endpoint to retrieve a new URL if they are unable to properly establish a
     * connection using the cached version of the URL.
     *
     * @return array
     */
    public function getGateway()
    {
        $url = \sprintf('%s/gateway', $this->apiUrl);

        return $this->execute($url);
    }

    /**
     * Returns an object based on the information in Get Gateway, plus additional metadata that can help during the
     * operation of large or sharded bots. Unlike the Get Gateway, this route should not be cached for extended periods
     * of time as the value is not guaranteed to be the same per-call, and changes as the bot joins/leaves guilds.
     *
     * @return array
     */
    public function getGatewayBot()
    {
        $url = \sprintf('%s/gateway/bot', $this->apiUrl);

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
    public function getUserFlagsArray($flag)
    {
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
    public function decodeSnowflake($snowflake)
    {
        return [
            'timestamp' => \round((($snowflake >> 22) + 1420070400000) / 1000),
            'internalWorkerID' => ($snowflake & 0x3E0000) >> 17,
            'internalProcessID' => ($snowflake & 0x1F000) >> 12,
            'increment' => $snowflake & 0xFFF,
        ];
    }

    /**
     * returns encoded permissions informations
     *
     * @param   integer $snowflake  Snowflake ID as decimal
     * @return array
     */
    public function permissionDecoder($permissions)
    {
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
            0x40000000 => 'MANAGE_EMOJIS',
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

    final protected function getHttpClient(): ClientInterface
    {
        if (!$this->httpClient) {
            $this->httpClient = HttpFactory::makeClient([
                RequestOptions::TIMEOUT => 2,
            ]);
        }

        return $this->httpClient;
    }

    /**
     * führt eine API-Anfrage aus
     *
     * @param   string  $url            URL der API-Anfrage
     * @param   string  $method         HTTP-Methode (Standard: GET)
     * @param   array   $parameters     Informationen die per Post oder JSON-Objekt an die API gesendet werden soll
     * @param   string  $contentType    Sendungstyp
     * @return  array
     */
    protected function execute(
        $url,
        $method = 'GET',
        $parameters = [],
        $contentType = 'application/x-www-form-urlencoded'
    ) {
        $reply = [];

        $headers = [
            'authorization' => $this->botType . ' ' . $this->botToken,
            'content-type' => $contentType,
        ];
        if ($method !== 'GET') {
            if (empty($parameters)) {
                $headers['content-length'] = 0;
            }
        }

        if ($contentType == 'application/x-www-form-urlencoded') {
            $parameters = \http_build_query($parameters, "", '&');
        } elseif ($contentType == 'application/json') {
            $parameters = JSON::encode($parameters);
        }

        $request = new Request($method, $url, $headers, $parameters);

        try {
            $response = $this->getHttpClient()->send($request);
            $reply = $this->parseReply($response);
        } catch (BadResponseException $e) {
            if (\ENABLE_DEBUG_MODE) {
                \wcf\functions\exception\logThrowable($e);
            }
            $reply = $this->parseReply($e->getResponse());
            $reply['error'] = [
                'message' => $e->getMessage(),
                'status' => $e->getCode(),
                'url' => $url,
                'method' => $method,
                'parameters' => $parameters,
                'contentType' => $contentType,
                'guildID' => $this->guildID,
                'botToken' => $this->botToken,
                'botType' => $this->botType,
            ];
        } catch (GuzzleException $e) {
            if (\ENABLE_DEBUG_MODE) {
                \wcf\functions\exception\logThrowable($e);
            }
            $reply = [
                'error' => [
                    'message' => $e->getMessage(),
                    'status' => $e->getCode(),
                    'url' => $url,
                    'method' => $method,
                    'parameters' => $parameters,
                    'contentType' => $contentType,
                    'guildID' => $this->guildID,
                    'botToken' => $this->botToken,
                    'botType' => $this->botType,
                ],
                'status' => 0,
                'body' => $e->getMessage(),
                'rateLimit' => [],
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
    protected function parseReply(ResponseInterface $response)
    {
        $body = (string)$response->getBody();
        try {
            $body = JSON::decode($body, true);
        } catch (Exception $e) {
        }
        $reply = [
            'error' => null,
            'status' => $response->getStatusCode(),
            'body' => $body,
            'rateLimit' => null,
        ];
        if ($response->hasHeader('x-ratelimit-limit')) {
            $reply['rateLimit']['limit'] = $response->getHeaderLine('x-ratelimit-limit');
        }
        if ($response->hasHeader('x-ratelimit-remaining')) {
            $reply['rateLimit']['remaining'] = $response->getHeaderLine('x-ratelimit-remaining');
        }
        if ($response->hasHeader('x-ratelimit-reset')) {
            $reply['rateLimit']['reset'] = $response->getHeaderLine('x-ratelimit-reset');
        }

        return $reply;
    }
}
