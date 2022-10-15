define(["require", "exports", "tslib", "WoltLabSuite/Core/Event/Handler"], function (require, exports, tslib_1, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DiscordGateway = void 0;
    EventHandler = tslib_1.__importStar(EventHandler);
    class DiscordGateway {
        constructor(token, presence = {}) {
            this.presence = {};
            this.heartbeatInterval = 0;
            this.seq = null;
            this.token = token;
            this.presence = presence;
            this.socket = new WebSocket("wss://gateway.discord.gg/?v=6&encoding=json");
            this.socket.addEventListener("open", () => this.onopen());
            this.socket.addEventListener("error", (ev) => this.onerror(ev));
            this.socket.addEventListener("message", (ev) => this.onmessage(ev));
            const data = {
                token: this.token,
                socket: this.socket,
            };
            EventHandler.fire("dev.hanashi.wsc.discord.gateway", "init", data);
        }
        onopen() {
            EventHandler.fire("dev.hanashi.wsc.discord.gateway", "onopen");
        }
        onerror(ev) {
            EventHandler.fire("dev.hanashi.wsc.discord.gateway", "onerror", ev);
        }
        onmessage(ev) {
            const data = JSON.parse(ev.data);
            switch (data.op) {
                case 0: {
                    this.seq = data.s;
                    EventHandler.fire("dev.hanashi.wsc.discord.gateway", "dispatch", data);
                    break;
                }
                case 10: {
                    // heartbeating
                    this.heartbeatInterval = data.d.heartbeat_interval;
                    this.sendHeartbeat();
                    EventHandler.fire("dev.hanashi.wsc.discord.gateway", "beforeVerify", data);
                    // verify
                    this.verify();
                    EventHandler.fire("dev.hanashi.wsc.discord.gateway", "afterVerify", data);
                    break;
                }
                case 11: {
                    this.sendHeartbeat();
                    break;
                }
                default: {
                    EventHandler.fire("dev.hanashi.wsc.discord.gateway", "unknownReceived", data);
                }
            }
        }
        sendHeartbeat() {
            setTimeout(() => this.heartbeat(), this.heartbeatInterval);
        }
        heartbeat() {
            const sendData = {
                op: 1,
                d: this.seq,
            };
            this.socket.send(JSON.stringify(sendData));
        }
        verify() {
            const sendData = {
                op: 2,
                d: {
                    token: this.token,
                    properties: {},
                    presence: this.presence,
                },
            };
            this.socket.send(JSON.stringify(sendData));
        }
    }
    exports.DiscordGateway = DiscordGateway;
    exports.default = DiscordGateway;
});
