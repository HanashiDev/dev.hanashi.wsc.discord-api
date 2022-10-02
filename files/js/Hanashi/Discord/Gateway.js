var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
define(["require", "exports", "WoltLabSuite/Core/Event/Handler"], function (require, exports, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DiscordGateway = void 0;
    EventHandler = __importStar(EventHandler);
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
